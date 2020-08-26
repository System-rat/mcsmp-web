use cli::RealtimeCli;
use futures::{
    prelude::*,
    stream::{SplitSink, SplitStream},
};
use lapin::{Channel, Connection, ConnectionProperties};
use redis::Client;
use std::sync::Arc;
use structopt::StructOpt;
use tokio::net::{TcpListener, TcpStream};
use tokio_amqp::LapinTokioExt;
use tokio_tungstenite::{
    accept_hdr_async,
    tungstenite::{
        handshake::server::{ErrorResponse, Request, Response},
        http::StatusCode,
    },
    WebSocketStream,
};

mod cli;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let opts = RealtimeCli::from_args();
    // WebScoket
    let mut server = TcpListener::bind(format!("{}:{}", opts.binding, opts.port)).await?;

    // RabbitMQ initialization
    let rabbitmq = Arc::new(
        Connection::connect(&opts.rabbitmq, ConnectionProperties::default().with_tokio()).await?,
    );

    // Ticketing server initialization
    let _ticketing_system = match opts.redis {
        Some(redis) => Client::open(redis)?.get_async_connection().await?,
        None => unimplemented!("Non redis ticketing system not yet implemented"),
    };

    // Handle stop request
    tokio::spawn(async {
        tokio::signal::ctrl_c().await.unwrap();
        println!("Woah there!");
        std::process::exit(0);
    });

    // Main WebSocket server
    loop {
        let (socket, addr) = server.accept().await.unwrap();
        let rbmq = rabbitmq.clone();
        tokio::spawn(async move {
            let websocket_result = accept_hdr_async(socket, handle_connection).await;
            match websocket_result {
                Ok(websocket) => {
                    let (mut sink, stream) = websocket.split();
                    let chan = rbmq.create_channel().await.unwrap();
                    process_websocket("test_exchange", stream, chan)
                        .await
                        .unwrap();
                }
                Err(e) => println!("An error occured at connection: {}", e),
            }
        });
    }
}

/// Handle the incomming WebSocket connection and authenticate
fn handle_connection(request: &Request, response: Response) -> Result<Response, ErrorResponse> {
    println!(
        "Connection received: {}",
        match request.uri().path_and_query() {
            Some(pq) => pq.as_str(),
            None => "",
        }
    );

    // TODO: Actually authenticate using ticketing system
    if request.uri().path().starts_with("/secret_code123") {
        Ok(response)
    } else {
        Err(Response::builder()
            .status(StatusCode::FORBIDDEN)
            .body(Some("Access denied".into()))
            .unwrap())
    }
}

async fn process_websocket(
    exchange: &str,
    mut stream_websocket: SplitStream<WebSocketStream<TcpStream>>,
    channel: Channel,
) -> Result<(), Box<dyn std::error::Error>> {
    use lapin::ExchangeKind;

    channel
        .exchange_declare(
            exchange,
            ExchangeKind::Topic,
            Default::default(),
            Default::default(),
        )
        .await?;

    while let Some(msg) = stream_websocket.next().await {
        match msg {
            Ok(m) => {
                if let Err(e) = channel
                    .basic_publish(
                        exchange,
                        "test_key",
                        Default::default(),
                        m.into_data(),
                        Default::default(),
                    )
                    .await
                {
                    println!("Error during publishing: {}", e);
                }
            }
            Err(e) => println!("An error occured at reading: {}", e),
        };
    }

    Ok(())
}
