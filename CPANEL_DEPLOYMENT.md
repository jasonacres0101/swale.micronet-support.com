# cPanel Deployment Guide

This Laravel CCTV monitoring app can run on cPanel, but it should be uploaded like a Laravel app, not like a plain PHP site.

## 1. Hosting Requirements

- PHP 8.3 or newer.
- Composer support, ideally available from cPanel Terminal or SSH.
- MySQL/MariaDB database recommended for cloud testing.
- Ability to set the domain document root to the app `public` folder.
- Cron jobs enabled for Laravel scheduled tasks.

## 2. Recommended Folder Layout

Best option:

```text
/home/CPANEL_USER/micronet-cctc-monitor
/home/CPANEL_USER/micronet-cctc-monitor/public
```

Point the test domain or subdomain document root to:

```text
/home/CPANEL_USER/micronet-cctc-monitor/public
```

Do not point the domain at the Laravel project root. Only the `public` folder should be web-accessible.

## 3. Upload Files

Upload the project files to:

```text
/home/CPANEL_USER/micronet-cctc-monitor
```

Do not upload these folders/files unless you specifically need them:

```text
node_modules
tests
.git
.phpunit.result.cache
```

You can upload `vendor` if cPanel cannot run Composer, but running Composer on the server is cleaner.

## 4. Build Assets Before Upload

Run locally before upload:

```bash
npm run build
```

Upload the generated `public/build` folder with the rest of the app.

## 5. Install PHP Dependencies

If cPanel has Terminal or SSH, run this from the app folder:

```bash
composer install --no-dev --optimize-autoloader
```

If Composer is not available on cPanel, upload the local `vendor` folder.

## 6. Create The Production .env File

Create `.env` on the cPanel server. Start from `.env.example`, then update these values:

```dotenv
APP_NAME="Micronet CCTV Monitor"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-test-domain.example.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_database_name
DB_USERNAME=cpanel_database_user
DB_PASSWORD=database_password

SESSION_DRIVER=file
SESSION_DOMAIN=
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
CACHE_STORE=file

HIKVISION_ALARM_TOKEN=replace-with-a-long-random-token
```

Keep the real `.env` file private. Do not put it in `public_html`.

## 7. Generate App Key

Run this once on the server:

```bash
php artisan key:generate
```

If you copy an existing `.env` with an `APP_KEY` already set, do not regenerate it unless this is a fresh test install.

## 8. Run Migrations

Run:

```bash
php artisan migrate --force
```

Optional for test/demo data:

```bash
php artisan db:seed --force
```

The seeded admin login is:

```text
admin@micronet.local
password
```

Change that password immediately on any cloud-accessible test site.

## 9. Storage Link

Run:

```bash
php artisan storage:link
```

This is needed for maintenance task image uploads.

If cPanel blocks symlinks, create a `public/storage` link through File Manager if supported, or ask the host to enable Laravel storage links.

## 10. Cache Production Config

Run after the `.env` file is correct:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Cron Job

Add this cPanel Cron Job to run every minute:

```bash
* * * * * cd /home/CPANEL_USER/micronet-cctc-monitor && php artisan schedule:run >> /dev/null 2>&1
```

This runs:

- Camera offline checks.
- Maintenance overdue checks.
- Recurring maintenance generation.

## 12. Hikvision Camera Alarm URL

Once uploaded, the camera alarm receiver URL will be:

```text
https://your-test-domain.example.com/api/hikvision/events
```

Camera settings:

- Method: `POST`
- Payload: XML, JSON, or multipart form data
- Header: `X-Hikvision-Token`
- Header value: same value as `HIKVISION_ALARM_TOKEN`

Make sure the camera network can reach the cloud server over HTTP/HTTPS.

## 13. File Permissions

These folders must be writable by PHP:

```text
storage
bootstrap/cache
```

Typical cPanel permissions:

- Folders: `755`
- Files: `644`

Some hosts need `775` for `storage` and `bootstrap/cache`.

## 14. If The Domain Cannot Point To /public

If cPanel cannot set the document root to the Laravel `public` folder, use this fallback:

1. Put the Laravel app outside `public_html`, for example:

```text
/home/CPANEL_USER/micronet-cctc-monitor
```

2. Copy the contents of:

```text
/home/CPANEL_USER/micronet-cctc-monitor/public
```

into:

```text
/home/CPANEL_USER/public_html
```

3. Edit `public_html/index.php` so the paths point back to the Laravel app:

```php
require __DIR__.'/../micronet-cctc-monitor/vendor/autoload.php';
$app = require_once __DIR__.'/../micronet-cctc-monitor/bootstrap/app.php';
```

Only use this fallback if you cannot change the domain document root.

## 15. Quick Cloud Test

After deployment:

1. Open the login page.
2. Login as the seeded admin if you seeded the database.
3. Open Settings > Hikvision > Camera setup guide.
4. Use the PowerShell test command generator.
5. Confirm the test event appears in Alarm admin.

## 16. Fix: Forbidden / Server Unable To Read .htaccess

If Apache shows:

```text
Forbidden
You don't have permission to access this resource.
Server unable to read htaccess file, denying access to be safe
```

This is normally a cPanel file permission, ownership, or document-root issue.

Check these first:

```bash
chmod 755 /home/CPANEL_USER
chmod 755 /home/CPANEL_USER/micronet-cctc-monitor
chmod 755 /home/CPANEL_USER/micronet-cctc-monitor/public
chmod 644 /home/CPANEL_USER/micronet-cctc-monitor/public/.htaccess
chmod 644 /home/CPANEL_USER/micronet-cctc-monitor/public/index.php
chmod -R 775 /home/CPANEL_USER/micronet-cctc-monitor/storage
chmod -R 775 /home/CPANEL_USER/micronet-cctc-monitor/bootstrap/cache
```

Also confirm:

- The domain document root is `/home/CPANEL_USER/micronet-cctc-monitor/public`.
- `public/.htaccess` exists on the server.
- `public/index.php` exists on the server.
- The files are owned by the cPanel account, not `root` or another user.
- The app is not inside a folder with permissions like `700`, because Apache must be able to traverse the parent folders.

If you uploaded with File Manager, select the folders/files and use **Permissions** to set:

- Folders: `755`
- Files: `644`
- `storage` and `bootstrap/cache`: writable by PHP, often `775`
