use cli::RealtimeCli;
use futures::prelude::*;
use lapin::{Connection, ConnectionProperties};
use structopt::StructOpt;
use tokio::net::TcpListener;
use tokio_amqp::LapinTokioExt;
use tokio_tungstenite::accept_async;

mod cli;

#[tokio::main]
async fn main() {
    let opts = RealtimeCli::from_args();
    let mut server: TcpListener = TcpListener::bind(format!("{}:{}", opts.binding, opts.port))
        .await
        .unwrap();
    let con = Connection::connect(&opts.rabbitmq, ConnectionProperties::default().with_tokio())
        .await
        .unwrap();
    con.create_channel().await.unwrap();
    tokio::spawn(async {
        tokio::signal::ctrl_c().await.unwrap();
        println!("Woah there!");
        std::process::exit(0);
    });

    loop {
        let (socket, _) = server.accept().await.unwrap();
        tokio::spawn(async move {
            let mut websocket = accept_async(socket).await.unwrap();
            for msg in websocket.next().await {
                println!("Got message: {}", msg.unwrap());
            }
        });
    }
}
