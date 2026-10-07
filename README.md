# Life of Pathi

A single Laravel 12 application with a private Filament 4 publishing dashboard, SQLite development database, Blade/Tailwind public pages, and an accessible SVG garden. **Still growing.** Demo records are explicitly labelled; they make no claims about Pathi’s clients, results or social accounts.

## Local setup

Requirements: PHP 8.4 with intl, mbstring, dom, pdo_sqlite, fileinfo, zip and curl; Composer 2; Node.js 22+ and npm. Alternatively use Docker Desktop (including on Windows).

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm ci
npm run build
php artisan pathi:admin
php artisan serve --host=127.0.0.1 --port=8000
```

The admin creation command asks for an email and a hidden password of at least 12 characters. No administrator or password is seeded, and public registration is disabled. Visit `/admin` and sign in. The seeder is repeatable and preserves edits to existing demo records. Do not run destructive `migrate:fresh` commands against a database you want to retain.

### Docker and Windows

For a beginner-friendly, Docker-only Windows workflow, follow [Windows setup](docs/windows-setup.md). Node/npm can run in the included `frontend` tool container; no separate host runtime is required.

```sh
docker compose build
docker compose run --rm app composer install
```

Copy `.env.example` to `.env` using Explorer or PowerShell `Copy-Item .env.example .env`. Then:

```sh
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan db:seed
docker compose run --rm frontend npm ci
docker compose run --rm frontend npm run build
docker compose run --rm app php artisan pathi:admin
docker compose up
```

On Windows, Docker Desktop with WSL2 is recommended. Node/npm can run in the frontend container; PHP/Composer run in the app container. The server binds only to loopback. `npm run dev` enables Vite while editing templates; `npm run build` generates production assets. Everyday dashboard publishing requires neither command.

### Database and storage

SQLite creates `database/database.sqlite` when migrating. `DB_CONNECTION=sqlite` is the default. In production configure PostgreSQL or MySQL connection variables; install the corresponding PDO extension. All uploaded files use Laravel’s **local private disk**, not `public/storage`. Do not run `storage:link` or expose `storage/app/private`. `/media/{id}?entry={id}` checks the associated entry’s publication and visibility each time. A CV or channel cover is intentionally publicly readable when explicitly selected. Archived/private entries lose attachment access immediately. Admins can inspect their files. Soft deletion preserves records and referenced media cannot be removed.

## Validation

```sh
php artisan test
vendor/bin/pint --test
npm run build
```

Tests cover public routes, relationships, reading order, admin access, previews, filters, curation, sanitisation, redirects, private files and visibility. See [Publishing guide](docs/publishing.md), [Architecture](docs/architecture.md), and [Cloud setup](docs/cloud-setup.md).

## Production prerequisites (no deployment performed)

- A PHP 8.4 web server pointing **only** to `public/`, persistent database and private storage, and built Vite/Filament assets.
- Set `APP_ENV=production`, `APP_DEBUG=false`, a securely generated `APP_KEY`, correct HTTPS `APP_URL`, secure session cookies and a trusted proxy configuration appropriate to your host.
- Install `composer install --no-dev --optimize-autoloader`, run `npm ci && npm run build`, `php artisan filament:assets`, `php artisan migrate --force` and `php artisan optimize` during release preparation. Do not run the development seeder on a live portfolio unless demonstration records are intended.
- Configure scheduled backups for the database and private files. Preserve APP_KEY in your secret manager. Do not commit `.env`, private storage, database files or administrator credentials.
- No queue worker, email service, external accounts or platform synchronisation is required. Contact uses editable email/social links; there is no enquiry form claiming mail delivery.
