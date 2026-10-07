# Run Life of Pathi on Windows

This guide uses Docker Desktop and VS Code. You do **not** need to install PHP, Composer, Node.js or a database separately. Docker runs those tools. Everything stays on your computer; these commands do not deploy a website.

## 1. Install the two programs

1. Install Docker Desktop for Windows from https://www.docker.com/products/docker-desktop/ . Use its recommended WSL 2 backend and **Linux containers**. Follow any Windows/WSL installation prompts and restart if requested. If Windows says WSL is missing, open PowerShell **as Administrator**, run `wsl --install`, and restart before continuing.
2. Install VS Code from https://code.visualstudio.com/ .
3. Open Docker Desktop and wait until its engine is running. Keep it open while using the project.

You need Internet access for the first installation and several GB of free disk space. A normal home Internet connection should work without special proxy configuration.

## 2. Extract and open the project

Right-click the downloaded source ZIP and choose **Extract All**. Choose `C:\Projects` as the extraction destination; the ZIP creates a `Life-of-Pathi` folder inside it. Avoid OneDrive/network folders. Open the extracted **Life-of-Pathi** folder in VS Code with **File → Open Folder**. The correct folder contains `compose.yaml`, `composer.json` and `.env.example` directly inside it.

In VS Code select **Terminal → New Terminal**. Choose **PowerShell** from the terminal dropdown. Run every command below in that terminal, one at a time, waiting for each to finish.

If you need to change folders manually:

```powershell
Set-Location "C:\Projects\Life-of-Pathi"
```

Check Docker:

```powershell
docker version
docker compose version
```

Both should display version information. An error connecting to the Docker engine means Docker Desktop is not ready.

## 3. First-time setup

Copy the example configuration. **Only do this once**; repeating it would overwrite your local settings and application key.

```powershell
Copy-Item .env.example .env
```

Build the PHP container. The first build can take several minutes:

```powershell
docker compose build app
```

Install the PHP dependencies (the first installation may take several minutes; Git is already included in the container):

```powershell
docker compose run --rm app composer install --prefer-dist --no-interaction
```

Create your local application encryption key:

```powershell
docker compose run --rm app php artisan key:generate
```

Create the SQLite database and its tables:

```powershell
docker compose run --rm app php artisan migrate --force
```

Add the labelled example branches, projects, articles and channels:

```powershell
docker compose run --rm app php artisan db:seed
```

Install the frontend dependencies and build its CSS/JavaScript. The `frontend` service is a tool container; it does not host a second website:

```powershell
docker compose run --rm frontend npm ci
docker compose run --rm frontend npm run build
```

Publish the dashboard assets:

```powershell
docker compose run --rm app php artisan filament:assets
```

Create your own administrator:

```powershell
docker compose run --rm app php artisan pathi:admin
```

Enter your email when asked, then a password of at least **12 characters**. Password input is hidden; not seeing characters appear is normal. No admin password is included in the ZIP. An existing email is not overwritten by this command.

## 4. Start the application

```powershell
docker compose up -d app
```

Open these addresses in your Windows browser:

- Website: http://localhost:8000
- Admin dashboard: http://localhost:8000/admin

Sign in with the account you just created. Read [Publishing guide](publishing.md) to replace the demonstration content. Publishing from the dashboard does not require rebuilding the frontend.

## 5. Stop and start later

Stop the application:

```powershell
docker compose down
```

Start again on another day, from the same project folder with Docker Desktop running:

```powershell
docker compose up -d app
```

You do **not** need to generate another key, recreate the database or reinstall dependencies each time. Your database and uploads live inside your extracted folder, so stopping containers does not remove them. Do not delete that folder or run `migrate:fresh` to troubleshoot: it would remove your content.

To create another local administrator later:

```powershell
docker compose run --rm app php artisan pathi:admin
```

## 6. Checks and troubleshooting

Inspect startup:

```powershell
docker compose ps
docker compose logs --tail 50 app
```

Run application tests and formatting checks:

```powershell
docker compose run --rm app php artisan test
docker compose run --rm app vendor/bin/pint --test
```

- **Port 8000 already in use:** stop the other application, or change `127.0.0.1:8000:8000` in `compose.yaml` to `127.0.0.1:8001:8000`, set `APP_URL=http://localhost:8001` in `.env`, and run `docker compose down` followed by `docker compose up -d app`. Then use port 8001 for both URLs.
- **Missing vendor/autoload.php:** run the Composer installation command above.
- **Missing Vite manifest or unstyled public pages:** run both frontend commands above.
- **Unstyled dashboard:** run `php artisan filament:assets` using the app tool command above.
- **No application encryption key:** generate the key during first setup. If you already have content, preserve your existing `.env` rather than replacing it.
- **SQLite file missing:** run the migration command; Laravel creates `database/database.sqlite`.
- **Docker download, proxy or certificate error:** check Docker Desktop’s network/proxy settings. Do not disable TLS verification. Corporate networks may need their organisation’s trusted CA configured in Docker.

After editing public templates, CSS or JavaScript in VS Code, rebuild with `docker compose run --rm frontend npm run build`. PHP/Blade changes are read from the mounted folder. All Docker commands here use the project’s `compose.yaml`; the cloud-specific `pathi-app` container name is **not** used on Windows.

## ZIP contents and your local data

The source archive contains application source, migrations, demo seeder, tests, lockfiles, Docker configuration, local fonts and guides. It excludes `.env`, Git history, installed dependencies, generated builds, SQLite databases, logs, sessions, caches and private uploads.

Never share your new `.env`, `database/database.sqlite`, `storage/app/private`, `vendor` or `node_modules`. Back up your `.env`, database and private uploads securely if you want to preserve local work. Public storage links are unnecessary: the application checks attachment access itself.
