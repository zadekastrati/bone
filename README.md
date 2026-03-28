# Laravel 10 — Posts, Events, Roles & Admin

Production-style starter with **MySQL**, **Blade + Tailwind (Vite)**, **session authentication**, **admin / user roles**, full **CRUD** for posts and events (with optional images, search, pagination), and **admin-only user management**.

## Requirements

- PHP **8.1+** and Composer
- MySQL **5.7+** / **8.x**
- Node.js **18+** and npm (for Vite / Tailwind)

## Setup (step by step)

1. **Clone or copy the project** into your environment.

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Environment file**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure MySQL** in `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bone_app
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

   Create the database (example):

   ```sql
   CREATE DATABASE bone_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Install front-end dependencies and build assets**

   ```bash
   npm install
   npm run build
   ```

   For local development with hot reload:

   ```bash
   npm run dev
   ```

6. **Public storage link** (required for uploaded post/event images)

   ```bash
   php artisan storage:link
   ```

7. **Migrate and seed**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

8. **Run the application**

   ```bash
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`.

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
- Use `php artisan config:cache` and `php artisan route:cache` in production after deployment.
