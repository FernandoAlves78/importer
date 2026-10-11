# CSV User Importer

Demo project: bulk-import users from a CSV file into MySQL, with a live progress bar, bilingual UI (English / Italian), and a ready-to-run Docker stack (PHP 8.4, MySQL, phpMyAdmin).

Built as a portfolio piece for technical interviews — focused, easy to run, and readable by experienced developers.

## Features

- Download a sample CSV with **50,000 users**
- Upload and validate CSV-only input
- Chunked PHP import (1,000 rows per request) with a **percentage progress bar**
- Summary of successful inserts and errors
- Table with the **last 5 registered users**
- UI language switcher: **EN | IT**
- Docker Compose: PHP 8.4 + Apache, MySQL 8, phpMyAdmin

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose v2)

## Quick start

```bash
cp .env.example .env
docker compose up -d --build
```

Open:

| Service    | URL                         |
|------------|-----------------------------|
| App        | http://localhost:8088       |
| phpMyAdmin | http://localhost:8089       |

Default DB credentials (see `.env.example`):

- Database: `importer`
- User: `importer`
- Password: `importer_secret`
- Root password: `root_secret`

Stop the stack:

```bash
docker compose down
```

## How to use

1. Open http://localhost:8088
2. Optionally switch language with **EN** / **IT** in the header
3. Click **Download sample CSV (50,000 users)** (generated on first download)
4. Choose the downloaded `.csv` file and click **Import users**
5. Watch the progress bar until completion
6. Review inserted count, any errors, and the last 5 users

You can confirm data in phpMyAdmin at http://localhost:8089.

## CSV format

Header (required, exact order):

```csv
name,email,phone,city,country
```

Example rows:

```csv
name,email,phone,city,country
Marco Rossi,marco.rossi@example.com,+39 320 1234567,Roma,Italy
Giulia Bianchi,giulia.bianchi@example.com,+39 333 7654321,Milano,Italy
```

Rules:

- Only `.csv` files are accepted
- `name` and `email` are required per row
- `email` must be unique (duplicates are reported as errors and skipped)

## Project structure

```
├── docker-compose.yml
├── Dockerfile
├── database/init.sql
├── public/
│   ├── index.php
│   ├── assets/css/app.css
│   ├── assets/js/i18n.js
│   ├── assets/js/app.js
│   └── api/
│       ├── upload.php
│       ├── process.php
│       ├── latest.php
│       └── sample.php
├── src/
│   ├── Config.php
│   ├── Database.php
│   ├── CsvValidator.php
│   ├── UserImporter.php
│   ├── SampleCsvGenerator.php
│   └── JsonResponse.php
├── scripts/generate_sample_csv.php
└── storage/
    ├── uploads/
    └── data/
```

## How the import works

1. `POST /api/upload.php` validates the file, stores it under `storage/uploads/`, returns a token and row count
2. The browser calls `POST /api/process.php` in batches until `done: true`
3. `GET /api/latest.php` returns the five most recent users
4. `GET /api/sample.php` generates (once) and downloads `users_50k.csv`

No Redis or job queue — chunked HTTP keeps the demo simple and portable.

## Tech stack

- PHP 8.4 (Apache)
- MySQL 8.0
- phpMyAdmin
- Vanilla JS (fetch + progress UI)
- Docker Compose

## License

MIT — feel free to use this demo in interviews and portfolios.
