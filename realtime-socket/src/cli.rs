use structopt::StructOpt;

/// The main application params
#[derive(Debug, StructOpt)]
#[structopt(
    name = "realtime-socket",
    about = "A realtime websocket for real time communication with the mcsmp connector"
)]
pub struct RealtimeCli {
    /// Use the builtin ticketing server instead of a redis server
    #[structopt(short = "t")]
    pub use_internal_ticketing: bool,

    /// Connection string for the redis server used for ticketing
    #[structopt(
        short,
        long,
        env = "REDIS_CONNECTION_STRING",
        required_unless("use-internal-ticketing")
    )]
    pub redis: Option<String>,

    /// Connection string for the RabbitMQ broker
    #[structopt(short = "q", long, env = "RABBITMQ_CONNECTION_STRING")]
    pub rabbitmq: String,

    /// WebSocket binding
    #[structopt(short, long, default_value = "0.0.0.0")]
    pub binding: String,

    /// WebSocket port
    #[structopt(short, long, default_value = "3621")]
    pub port: i32,
}
