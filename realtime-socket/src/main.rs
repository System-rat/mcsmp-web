use cli::RealtimeCli;
use futures::prelude::*;
use lapin::{Connection, ConnectionProperties};
use structopt::StructOpt;
use tokio::net::TcpListener;
use tokio_amqp::LapinTokioExt;
use tokio_tungstenite::accept_hdr_async;
use tokio_tungstenite::tungstenite::handshake::server::{ErrorResponse, Request, Response};
use tokio_tungstenite::tungstenite::http::StatusCode;

mod cli;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let opts = RealtimeCli::from_args();
    let mut server = TcpListener::bind(format!("{}:{}", opts.binding, opts.port)).await?;
    let con =
        Connection::connect(&opts.rabbitmq, ConnectionProperties::default().with_tokio()).await?;
    let _chan = con.create_channel().await?;
    tokio::spawn(async {
        tokio::signal::ctrl_c().await.unwrap();
        println!("Woah there!");
        std::process::exit(0);
    });

    loop {
        let (socket, addr) = server.accept().await.unwrap();
        tokio::spawn(async move {
            let mut websocket = accept_hdr_async(socket, handle_connection)
                .await
                .map_err(|err| println!("An error occured: {}", err))
                .unwrap();
            while let Some(msg) = websocket.next().await {
                println!("Got message from {}: {}", addr, msg.unwrap());
            }
        });
    }
}

fn handle_connection(request: &Request, response: Response) -> Result<Response, ErrorResponse> {
    println!(
        "Connection received: {}",
        match request.uri().path_and_query() {
            Some(pq) => pq.as_str(),
            None => "",
        }
    );

    if request.uri().path().starts_with("/secret_code123") {
        Ok(response)
    } else {
        Err(Response::builder()
            .status(StatusCode::FORBIDDEN)
            .body(Some("Access denied".into()))
            .unwrap())
    }
}
