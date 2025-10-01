# echoQuiz.eu

hosted on [echoquiz.eu](https://echoquiz.eu)

Software created by [Benedikt Brünner, MEd BEd](https://brünner.at)

Licence for this Software is [MIT](./LICENSE)

## Publication

ARS Structure is CC BY 4.0 Benedikt Brünner and Martin Ebner, [ed-tech.at](https://ed-tech.at).

Paper accepted at ICL2025, currently in revision.

## Setup

Copy the `/web/php/pws.example.php` to `/web/php/pws.php` and adapt it to your needs and run

```
cd docker
docker compose build
docker compose up -d
```

If `mariadb_net` is missing, create it with

```
docker network create mariadb_net
```

## MariaDB Setup
Import the SQL Dump from `/docker/db/echoquiz_import.sql`

If you do not have a mariadb, you can use the `docker/db/docker-compose.yml` and run

```
cd docker/db
docker compose up -d
```

and open http://localhost:3002/ with user `root` and `rootpass` and run the SQL

```
-- Create the user
CREATE USER 'echoquiz'@'%' IDENTIFIED BY 'NOGIT';

-- Create the database
CREATE DATABASE echoquiz;

-- Grant privileges to the user on the database
GRANT ALL PRIVILEGES ON echoquiz.* TO 'echoquiz'@'%';

-- Apply changes
FLUSH PRIVILEGES;
```


## Further Licenses
Licences for included software (NchanSubscriber.js, jQuery, htmx, nchan) is located at [/web/files/licenses](./web/files/licenses)
