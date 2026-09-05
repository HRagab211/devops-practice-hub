# DevOps Practice Hub

A small Laravel monolith for practicing infrastructure with real application activity. Blade, Tailwind 4, and Vite power the interface; Fortify handles registration, login, logout, and password resets. No deployment infrastructure is included.

## Requirements

- PHP **8.4+** for the committed dependency set (tested with 8.4.23), Composer 2.
- Laravel **13.30.1**, Fortify **1.39.0**, Pest **5.1.3**, pinned by `composer.lock`.
- Node **22.12+** (tested with 22.13.1), npm, Vite 8, pinned by `package-lock.json`.
- MySQL 8+. PHP extensions: PDO, pdo_mysql, mbstring, openssl, tokenizer, XML/DOM, ctype, curl, fileinfo, filter, hash, session; GD for image test fixtures and pdo_sqlite for isolated tests. CLI workers need pcntl/posix for process signals and enforced job timeouts.
- Web process write access to `storage` and `bootstrap/cache`; web document root must be `public`.

## Setup

```sh
composer install
npm ci
cp .env.example .env # Only on a fresh clone; preserve an existing .env.
php artisan key:generate
```

Create the database with a MySQL administrator, replacing the password:

```sql
CREATE DATABASE devops_practice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'devops_practice'@'localhost' IDENTIFIED BY 'replace-with-local-password';
GRANT ALL PRIVILEGES ON devops_practice.* TO 'devops_practice'@'localhost';
```

Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` to match your server. Use an account host appropriate to your MySQL connection. An existing skeleton `.env` may still select SQLite: explicitly set `DB_CONNECTION=mysql`. Never regenerate an established production APP_KEY.

```sh
php artisan config:clear
php artisan migrate --no-interaction
php artisan storage:link
npm run build
# Optional, explicitly local/testing only:
php artisan db:seed --class=DemoSeeder --no-interaction
```

The demo login is `demo@example.com` / `password`. The demo seeder refuses environments other than local/testing and is safe to rerun. The default seeder creates no accounts.

Run these in separate terminals:

```sh
php artisan serve
npm run dev
php artisan queue:work --queue=default --tries=3 --timeout=60 --sleep=1
php artisan schedule:work
```

The Vite development process is optional after `npm run build`. Keep the worker separate when practicing stopped-worker behavior. Open the address printed by `artisan serve`, then register or use the optional demo account.

## Configuration and service exercises

All environment reads live in configuration files and work with `php artisan config:cache`.

| Service | Local setting | Application activity | Future option |
| --- | --- | --- | --- |
| Database | `DB_CONNECTION=mysql` | Users, tasks, reports, heartbeat | Separate MySQL / RDS |
| Sessions | `SESSION_DRIVER=database` | Login persistence | `redis` |
| Cache | `CACHE_STORE=database` | Per-user task counts cached for 60 seconds, invalidated after committed task changes | `redis` |
| Queue | `QUEUE_CONNECTION=database` | Report CSV jobs and welcome mail | `redis` |
| Public files | `PROFILE_IMAGE_DISK=public` | JPEG/PNG/WebP avatars, up to 2 MB | Public S3-compatible disk |
| Private files | `REPORT_DISK=reports` | Authorized CSV downloads | Private S3-compatible disk |
| Email | `MAIL_MAILER=log` | Welcome mail and reset links | SMTP / Mailpit |
| Scheduler | Separate scheduler process | One heartbeat row updated every minute | Separately managed scheduler |

Disk names and relative paths are persisted per upload/report. Existing files continue using their original disk after a default changes; migrate files and stored references together when moving existing data. Local reports live in `storage/app/reports`, outside the public storage link. Do not link that directory publicly. Downloads always pass through authentication and report policy checks. CSV formula-leading cells are prefixed with an apostrophe; multiline text is properly quoted.

Configure optional S3 storage with `composer require league/flysystem-aws-s3-v3:^3.0`, AWS credentials, bucket, endpoint, region, and path-style settings. Define separate public and private disks/buckets where necessary; keep reports private. Avatar URLs require public object access or an appropriate CDN. S3-compatible backends that do not support ACLs need disk-specific visibility configuration.

Redis requires the PHP `redis` extension with `REDIS_CLIENT=phpredis`, or `composer require predis/predis` and `REDIS_CLIENT=predis`. Configure shared Redis connections before switching sessions, cache, or queue. MySQL connection timeout defaults to 3 seconds; Redis connect/read timeouts default to 3 seconds with no automatic connection retries. Set bounded timeouts on any additional connection/store you add.

`APP_VERSION`, `APP_COMMIT_SHA`, and `APP_INSTANCE` identify releases and instances without shell commands. Their local fallbacks are `local`, `unreleased`, and `local`. `LAB_TOOLS_ENABLED` defaults to true only for `APP_ENV=local`; the example enables it explicitly. Set it to `false` in production. Set `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, and the correct HTTPS `APP_URL` in production. Session cookies are HTTP-only and SameSite=Lax; the example enables session encryption.

Select `LOG_CHANNEL=stderr` for container logs, or keep the default single-file stack locally. Job logs include the report identifier, attempt, and errors; public responses do not expose exception details.

## Observe jobs, mail, and scheduling

1. Stop all queue workers. Request **Generate my task report** on Reports. Refresh: its persisted status remains **pending**.
2. Start the queue worker command above. Refresh Reports: the status changes through **processing** to **completed** and a download appears. Small reports may finish before you can observe processing.
3. The Lab page shows your recent report records, not a guessed worker status. Jobs have three attempts with 10/30/60-second backoff and a 60-second timeout; the default 90-second queue reservation exceeds that timeout. Report retries reuse the same row and path. Exhausted failures become **failed**. After fixing a dependency, use `php artisan queue:failed` and `php artisan queue:retry <uuid>`, or request a new report.
4. Welcome emails are queued; start a worker to deliver them to the log mailer. Password-reset emails are logged immediately. Inspect `storage/logs/laravel.log` (for example `tail -f storage/logs/laravel.log`) to find email bodies and reset links. Logs may contain reset tokens: keep them private.
5. Start `php artisan schedule:work`, wait a minute, and refresh Lab. The heartbeat timestamp advances. `php artisan lab:heartbeat` runs the command manually; `php artisan schedule:list` shows its schedule. A manual heartbeat proves command execution, while continuing updates prove scheduler activity.

## Health checks

- `/up`: Laravel application liveness, independent of database/cache availability.
- `/health/ready`: unauthenticated, without session middleware. Performs a database query and a unique short-lived write/read/delete probe against the configured cache. Returns only `{"ready":true}` with HTTP 200 or `{"ready":false}` with HTTP 503. Responses are not cached and contain no credentials or exception details.
- `/lab`: authenticated and flag-controlled; shows actual dependency results, release metadata, the heartbeat, and only the current user's reports. Its database session means a complete database outage may prevent opening Lab; use readiness for outage probes.

These endpoints do not establish worker availability. Driver connection/read timeouts bound connections; they are not an operating-system deadline for DNS or every possible backend operation. Configure web/proxy request deadlines when adding infrastructure.

## Verification

```sh
php artisan test --compact
npm run build
vendor/bin/pint --format agent
```

Tests use SQLite `:memory:`, array sessions/cache, and fake storage/mail where appropriate. One integration test dispatches onto the real database queue and executes a worker. Tests never reset the application's database. Do not override test database settings with application credentials. For MySQL-specific testing, use a dedicated disposable database and separate test configuration.

Verification during implementation: focused tests, full suite, production build, and source formatting are run and reported in the delivery summary. MySQL integration requires a running local server. No browser automation dependency is installed. Without `.git`, Pint's `--dirty` option cannot run; use the full command above. Commit `composer.lock`, `package-lock.json`, and `.env.example`; secrets, runtime files, uploads, reports, logs, vendor, and node_modules are ignored.

## Future scaling

Use shared sessions and cache, shared public/private file storage, and one consistent APP_KEY across instances. Manage web, queue worker, and scheduler processes separately. Run one scheduler instance (or add shared scheduler locks for additional instances); the heartbeat upsert remains a single row. Restart workers after deploying code/config changes. Configure S3 network deadlines and keep queue reservation/visibility timeouts longer than execution timeouts. Keep backups, failed-job retention, report retention, and private file lifecycle policies as explicit operational choices. Docker, Kubernetes, Helm, CI/CD, Terraform, Ansible, and monitoring configurations are intentionally left for your practice.
