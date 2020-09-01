# MC-SMP Web

A web management system for MineCraft servers using the [mcsmp](https://github.com/System-rat/mcsmp) gem

# Running

## Backend

Running the backend requires PHP >7.4 with composer installed, a MySQL database and the Symfony CLI 

Clone the repository
```sh
git clone https://github.com/System-rat/mcsmp-web
cd mcsmp-web
```

Install the required composer packages
```sh
composer install
```

Create a `.env.local` file with the MySQL database connection string
```sh
echo "mysql://user:password@localhost/mcsmp" > .env.local
```

Run the database migrations
```sh
php bin/console doctrine:migrations:migrate
```

Run the backend (The `--allow-http` flag is so we can have an unsecure connection from the webpack dev-server for
testing, the `-d` flag detaches the output from the terminal so it can be reused)
```sh
symfony server:start --allow-http -d
```

## Frontend

Running the frontend requires nodejs (The latest LTS probably) and yarn

Install all the dependencies
```sh
yarn
```

Run the dev-server
```sh
yarn dev-server
```

# Current features

Currently the management platform has the following features:
 - Basic user login (Currently the user must be predefined in the DB)
 - Adding connectors through the `Servers` tab
 - Creating servers on a connector (can choose a specific version)
 - Basic server management:
    - Start, stop, restart a server
    - Set a specific version
    - Download the latest version, or the latest snapshot
    - Change the server properties
    - Delete the server

# Project structure

The project is structured as follows (Will be changed at some point for a cleaner layout)
 - `frontend` contains the frontend code for the Vuejs single page app
 - `realtime-socket` is for the planned realtime communication socket for handling events from the connector
 - Everything else belongs to the backend

# License

This project is available under the [MIT License](https://opensource.org/licenses/MIT).