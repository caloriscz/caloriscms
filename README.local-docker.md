# Local Docker Setup

This runs the legacy Caloris CMS stack without installing PHP, Composer, Node, or MySQL on the host.

## Start

```powershell
docker compose build
docker compose up -d
```

The first `app` start installs Composer dependencies into the `composer_vendor` Docker volume.

Open:

- Frontend: http://localhost:8090
- Admin: http://localhost:8090/admin

## Reset Local Admin Password

The seed database contains an `admin` user, but the historical password is not documented. Reset it locally:

```powershell
docker compose --profile tools run --rm reset-admin
```

Then log in with:

- Username: `admin`
- Password: `admin`

## Database

- Host from host machine: `127.0.0.1`
- Port from host machine: `3307`
- Host from Docker services: `db`
- Database: `caloriscms`
- User: `caloris`
- Password: `caloris`
- Root password: `caloris_root`

Optional database UI:

```powershell
docker compose --profile tools up -d phpmyadmin
```

Open http://localhost:8081.

## Reset All Local Data

```powershell
docker compose down -v
docker compose up -d
```

The SQL dump at `app/model/db_mysql.sql` is imported only when the DB volume is first created.
