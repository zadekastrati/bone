# Laravel 10 — Posts, Events, Roles & Admin

Production-style starter with **MySQL**, **Blade + Tailwind (Vite)**, **session authentication**, **admin / user roles**, full **CRUD** for posts and events (with optional images, search, pagination), and **admin-only user management**.

## Requirements

- **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (Windows/Mac) or Docker Engine + Compose (Linux)
- **Git**

PHP, Composer, Node, and MySQL run **inside** containers ([Laravel Sail](https://laravel.com/docs/sail)); you do not install them on the host.

## Setup

Stack: **`compose.yaml`** — PHP 8.2, MySQL 8.4, Mailpit.

1. **Clone and enter the project**

   ```bash
   git clone <YOUR_REPO_URL> bone
   cd bone
   ```

2. **Environment**

   ```bash
   cp .env.example .env
   ```

   `.env.example` is already configured for Sail (MySQL and Mailpit service names). Uncomment `WWWGROUP` / `WWWUSER` on Linux if file permissions are wrong inside the container. If port **80** is taken, add `APP_PORT=8080` to `.env`. If **3306** is already used on the host (e.g. Laragon MySQL), keep `FORWARD_DB_PORT=3307` in `.env` (already set) so Docker can bind MySQL on **3307** instead — Laravel inside the container still uses `mysql:3306`.

3. **Install Composer dependencies** (one-off container — no local PHP/Composer needed)

   **macOS / Linux / Git Bash:**

   ```bash
   docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php82-composer:latest composer install --ignore-platform-reqs
   ```

   **Windows PowerShell** (run from the project folder):

   ```powershell
   docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php82-composer:latest composer install --ignore-platform-reqs
   ```

4. **Start the stack**

   ```bash
   docker compose up -d
   ```

5. **Key, assets, storage, database**

   Use **one terminal** (PowerShell, Git Bash, Terminal, etc.). Run the lines **in order**, one after another — or copy the whole block and paste once; your shell will run each line in sequence.

   ```bash
   docker compose exec laravel.test php artisan key:generate
   docker compose exec laravel.test npm install
   docker compose exec laravel.test npm run build
   docker compose exec laravel.test php artisan storage:link
   docker compose exec laravel.test php artisan migrate --seed
   ```

   Optional one-liner (same result, stops if any step fails):

   ```bash
   docker compose exec laravel.test php artisan key:generate && docker compose exec laravel.test npm install && docker compose exec laravel.test npm run build && docker compose exec laravel.test php artisan storage:link && docker compose exec laravel.test php artisan migrate --seed
   ```

6. **Open**

   - App: **http://localhost** (if port 80 is busy, set `APP_PORT=8080` in `.env` → **http://localhost:8080**)
   - Mailpit: **http://localhost:8025**
   - Optional — Vite hot reload (second terminal): `docker compose exec laravel.test npm run dev`

7. **Stop**

   ```bash
   docker compose down
   ```

   On **macOS/Linux**, if you prefer the Sail helper: `./vendor/bin/sail` works the same way (`sail up`, `sail artisan`, …). On **Windows**, `docker compose` + `docker compose exec laravel.test` avoids Bash/WSL issues with the `sail` script.

## Default seeded accounts

| Email             | Password   | Role  |
|-------------------|------------|-------|
| admin@example.com | password   | admin |
| user@example.com  | password   | user  |

Seeding also creates **5 sample posts** and **5 sample events** (see `PostSeeder` and `EventSeeder`).

## Features

- Register / login / logout; new registrations are always **user** role.
- **Admin**: full access to all posts and events; **Users** CRUD at `/admin/users`.
- **User**: full CRUD on **own** posts and events; can view all posts/events.
- Validation via **Form Requests**; **flash** success messages; **$errors** summary in the layout.
- **Search** + **pagination** on posts and events lists; **optional image** uploads (stored on the `public` disk).

## Project notes

- Set `APP_URL` in `.env` to your real URL in production so storage URLs resolve correctly.
- Production deploys without Sail: build assets and run `php artisan optimize`, `config:cache`, `route:cache` as usual on the server.
