# Apply the woodland redesign to your existing Windows ZIP installation

This is an update, not first-time setup. Your installation is at:

```text
E:\DownloadsC\Life-of-Pathi-portfolio-site-work\Life-of-Pathi-portfolio-site-work
```

The update ZIP contains only changed application code, artwork, tests and documentation. It includes no `.env`, database, uploads or installed dependencies. No migrations or dependency changes are required for this redesign. Do not run initial seeding, `migrate:fresh`, `key:generate`, or copy `.env.example` over `.env`.

## 1. Stop the application and back up your local data

Open Docker Desktop, using Linux containers. Open your existing project folder in VS Code. Choose **Terminal → New Terminal → PowerShell** and run:

```powershell
Set-Location 'E:\DownloadsC\Life-of-Pathi-portfolio-site-work\Life-of-Pathi-portfolio-site-work'
docker compose down
```

With the app stopped, make a backup of `.env`, the entire `database` folder and `storage\app` using File Explorer. Store that backup outside the project folder. `storage\app` contains your uploads. Keep the backup private. The update script also makes a separate backup of the code files it replaces.

## 2. Extract the update ZIP and apply its code

Download **Life-of-Pathi-woodland-update.zip**, right-click it, select **Extract All**, and extract it into `E:\DownloadsC`. This creates `E:\DownloadsC\Life-of-Pathi-woodland-update`. Do not extract it directly over your project.

In the same PowerShell terminal:

```powershell
Set-Location 'E:\DownloadsC\Life-of-Pathi-woodland-update'
powershell -NoProfile -ExecutionPolicy Bypass -File '.\Apply-Woodland-Update.ps1' -ProjectPath 'E:\DownloadsC\Life-of-Pathi-portfolio-site-work\Life-of-Pathi-portfolio-site-work'
```

The execution-policy flag applies only to this process; it does not change Windows' permanent policy. Administrator access is unnecessary. The script checks every payload checksum and its allowed path before copying. It replaces only the listed code/artwork files and verifies that an existing `.env` and SQLite database remain unchanged. It never copies into your storage or dependency directories. It prints the location of the previous-code backup.

If your organisation blocks PowerShell scripts, manually copy the contents of the extracted `payload` folder into the existing project, merging folders and replacing only matching code files. The payload contains no local data. Keep your own backup first.

## 3. Rebuild assets and start

Return to the existing project folder:

```powershell
Set-Location 'E:\DownloadsC\Life-of-Pathi-portfolio-site-work\Life-of-Pathi-portfolio-site-work'
docker compose run --rm frontend npm ci
docker compose run --rm frontend npm run build
docker compose run --rm app php artisan optimize:clear
docker compose up -d app
```

These commands use your existing `.env`, application key, SQLite database, admin account and uploads. The existing PHP image and Composer dependencies are sufficient; there are no new PHP dependencies. `npm ci` installs the same locked frontend dependencies and the next command compiles the new design.

Open http://localhost:8000 and hard-refresh with **Ctrl+F5**. Your dashboard remains at http://localhost:8000/admin. If you previously configured another port, use that port instead.

## 4. Check your content

Confirm your existing projects and uploaded images appear, then sign in using your existing administrator. Test a branch preview and open its project or tutorial. The site should still show all your records. If the app fails to start:

```powershell
docker compose logs --tail 50 app
```

Missing frontend manifest: repeat the frontend build. Missing `vendor/autoload.php`: restore/install the locked dependencies with `docker compose run --rm app composer install --prefer-dist --no-interaction`. Neither action resets data.

## Managing the tree in Filament

- **Branches:** name, description, parent, activity and sort order control labels, previews, smaller branches and ordering. Renaming preserves marker placement. Reordering intentionally changes placement. New branches appear automatically.
- Up to eight main branches appear together. Additional branch groups have simple buttons below the scene. All smaller branches, including Crochet under Making & Practice, remain accessible in previews and the complete branch index. Dormant branches use quieter markers; archived branches stay out of discovery.
- **Projects:** connect a primary branch or additional branches, upload imagery, and choose maturity. `fruit` appears as a warm completed-outcome marker; ongoing projects use smaller green growth. Up to two project markers and three content links appear per main branch, with the full collection one click away.
- **Articles, videos, channels and project relationships:** use the existing resources. Published connections appear in the branch previews, collection pages and project journeys. The existing private, draft, unlisted and public rules are preserved.
- **Settings:** introduction, tagline, Now content, contact information and featured pathway continue to control their existing destinations. The default title is Life of Pathi and the default tagline is Still growing.

No coordinates, new visual controls or code changes are required for routine publishing.

## Roll back the design if needed

Stop Docker, copy the previous code files from the printed `Life-of-Pathi-code-backup-*` directory into your project, rebuild the frontend, clear Laravel caches and restart. New artwork files may remain unused. Do not restore or replace your live database for a design-only rollback.
