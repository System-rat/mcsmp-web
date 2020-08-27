use cli::RealtimeCli;
use futures::{
    prelude::*,
    stream::{SplitSink, SplitStream},
};
use lapin::{
    types::ShortString, Channel, Connection, ConnectionProperties, Consumer, ExchangeKind,
};
use std::sync::Arc;
use structopt::StructOpt;
use tokio::{
    net::{TcpListener, TcpStream},
    select,
    sync::{
        mpsc,
        watch::{channel, Receiver, Sender},
    },
};
use tokio_amqp::LapinTokioExt;
use tokio_tungstenite::{
    accept_hdr_async,
    tungstenite::{
        handshake::server::{ErrorResponse, Request, Response},
        http::StatusCode,
        Message,
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
    /*let _ticketing_system = match opts.redis {
        Some(redis) => Client::open(redis)?.get_async_connection().await?,
        None => unimplemented!("Non redis ticketing system not yet implemented"),
    };*/

    // Handle stop request
    tokio::spawn(async {
        tokio::signal::ctrl_c().await.unwrap();
        println!("Woah there!");
        std::process::exit(0);
    });

    // Main WebSocket server
    loop {
        let (socket, _addr) = server.accept().await.unwrap();
        let rbmq = rabbitmq.clone();
        tokio::spawn(async move {
            let websocket_result = accept_hdr_async(socket, handle_connection).await;
            match websocket_result {
                Ok(websocket) => {
                    let (sink, stream) = websocket.split();
                    let chan = rbmq.create_channel().await.unwrap();
                    process_websocket("test_exchange", stream, sink, chan)
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
    mut sink_websocket: SplitSink<WebSocketStream<TcpStream>, Message>,
    chan: Channel,
) -> Result<(), Box<dyn std::error::Error>> {
    chan.exchange_declare(
        exchange,
        ExchangeKind::Topic,
        Default::default(),
        Default::default(),
    )
    .await?;

    // (name, consumer_tag, cancel_token)
    let mut topic_subscriptions: Vec<(&str, ShortString, Sender<bool>)> = vec![];
    let (mut s, mut r) = mpsc::channel(20);
    let (cancel_s, cancel_r) = channel(false);

    let websocket_receiver = tokio::spawn(async move {
        while let Some(m) = r.recv().await {
            if let Message::Close(..) = m {
                println!("Closing socket...");
                cancel_s.broadcast(true).expect("Error during mass cancel");
                break;
            }
            println!("Got message: {}", m);
            sink_websocket.send(m).await.expect("Error during sending");
        }
    });

    while let Some(msg) = stream_websocket.next().await {
        match msg {
            Ok(m) => {
                match &m {
                    Message::Close { .. } => {
                        println!("Connection closed");
                        break;
                    }
                    Message::Text(text) => {
                        if text.starts_with("SUB") {
                            let queue = chan
                                .queue_declare("", Default::default(), Default::default())
                                .await
                                .expect("Error creating queue");
                            chan.queue_bind(
                                queue.name().as_str(),
                                "test_exchange",
                                "events.all",
                                Default::default(),
                                Default::default(),
                            )
                            .await
                            .expect("Error binding queue");
                            let consumer = chan
                                .basic_consume(
                                    queue.name().as_str(),
                                    "",
                                    Default::default(),
                                    Default::default(),
                                )
                                .await
                                .expect("Could not create consumer");

                            tokio::spawn(handle_consume(s.clone(), consumer, cancel_r.clone()));
                        }
                    }
                    _ => {}
                };
                if let Err(e) = chan
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
    s.send(Message::Close(None))
        .await
        .expect("Error during closing");
    websocket_receiver.await.expect("Error during joining");

    Ok(())
}

async fn handle_consume(
    mut sender: mpsc::Sender<Message>,
    mut consumer: Consumer,
    mut cancel_r: Receiver<bool>,
) {
    loop {
        select! {
            c = cancel_r.recv() => {
                if let Some(cr) = c {
                    if cr {
                        break;
                    }
                }
            },
            message = consumer.next() => {
                match message {
                    Some(m) => {
                        let (chan, delivery) = m.expect("Error in consumer");
                        sender.send(Message::Binary(delivery.data)).await.expect("Error receiving value");
                        chan.basic_ack(delivery.delivery_tag, Default::default()).await.expect("Error during ACK");
                    }
                    None => break
                }
            }

        }
    }
}
