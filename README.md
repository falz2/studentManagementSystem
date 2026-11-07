# Student Management System

A Laravel application for managing students, courses, lecturers, and course registrations. It uses Laravel Jetstream with Livewire for authentication and account features.

## Requirements

- PHP 8.2 or later, with the SQLite PDO extension enabled
- Composer
- Node.js and npm

## Installation

From the project directory, create the SQLite database file and run the project's setup script:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates `.env` if needed, generates the application key, runs database migrations, and builds frontend assets.

Run database migrations and seed the sample accounts, students, lecturers, and courses:

```bash
php artisan migrate --seed
```

The seeder creates five students, four courses, one admin account, and three lecturer accounts. Seeding is safe to repeat.

Start the application and its development services:

```bash
composer run dev
```

Open the URL shown by Laravel (normally `http://127.0.0.1:8000`). To use a database other than SQLite, update the `DB_*` values in `.env` for your database and create the database before running migrations.

## Roles and sample login

Roles and user assignments are managed with [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission). Authenticated routes are restricted to:

- **Admin (`admin`)** — manages students, courses, lecturers, and registrations.
- **Lecturer (`lecturer`)** — accesses the lecturer dashboard.
- **Student (`student`)** — accesses the student dashboard and course registration.

Public registration always assigns the `student` role. The database seeder creates these sample accounts; they all use the development password `password`:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@northstar.edu` | `password` |
| Lecturer | `elena.vasquez@northstar.edu` | `password` |
| Lecturer | `samuel.okafor@northstar.edu` | `password` |
| Lecturer | `aisha.rahman@northstar.edu` | `password` |
| Student | `maya.chen@northstar.edu` | `password` |
| Student | `jordan.okello@northstar.edu` | `password` |
| Student | `amara.ndlovu@northstar.edu` | `password` |
| Student | `leo.kimani@northstar.edu` | `password` |
| Student | `priya.patel@northstar.edu` | `password` |

To grant a role to another registered account, use a trusted console:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'your-email@example.edu')->firstOrFail();
$user->assignRole('admin');
```

Use `lecturer` or `student` as needed. Do not expose role assignment through public registration. Change the sample passwords before using these accounts outside local development.

## Database design

The schema, entity relationships, integrity rules, and recommended path for adding academic terms are documented in [docs/database-design.md](docs/database-design.md).
