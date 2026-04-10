# Academic Management System (Laravel 12)

Enterprise-ready modular platform built with:

- Laravel 12
- MySQL
- Tailwind CSS (maroon/red premium theme)
- RBAC via `spatie/laravel-permission`
- Queue-first architecture (database by default, Redis-ready)

## Quick Start

1. Install PHP dependencies:
    - `composer install`
2. Install frontend dependencies:
    - `npm install`
3. Copy env file and set MySQL values:
    - `copy .env.example .env`
4. Generate app key:
    - `php artisan key:generate`
5. Run schema and seed base RBAC data:
    - `php artisan migrate --seed`

## Run Locally

- Full dev stack (server + queue + Vite):
  - `composer run dev`
- App only:
  - `php artisan serve`
- Queue worker only:
  - `php artisan queue:work`
- Frontend watcher only:
  - `npm run dev`

Default local URLs:

- App: `http://127.0.0.1:8000`
- Vite: `http://localhost:5173`

## Modules

Code is organized under `Modules/`:

- `Auth`
- `User`
- `Course`
- `Programme`
- `Group`
- `Workflow`
- `Examination`
- `Notification`
- `Integration`

Each module follows clean architecture:

- Controller -> Service -> Model
- Form Requests for validation
- Policies for authorization
- DTO for workflow/exam write paths

## Queue & Background Jobs

Default queue driver is database (Windows-friendly):

- `QUEUE_CONNECTION=database`
- Queue worker command:
    - `php artisan queue:work`

Queue tables:

- `jobs`
- `failed_jobs`

Notifications and heavy workflow events are queued through listeners and queued notifications.

### Switch to Redis in Production

No business logic refactor required. Change environment variables only:

- `QUEUE_CONNECTION=redis`
- `REDIS_QUEUE_CONNECTION=default`
- `REDIS_QUEUE=default`

## Notification Channels

Implemented:

- In-app (`database` channel)
- Email (`mail` channel)
- Telegram (optional custom channel)

Push ready:

- FCM service placeholder in `Modules/Notification/Services/PushNotificationService.php`

## RBAC Roles

Seeded roles:

- `admin`
- `lecturer`
- `coordinator`
- `reviewer`
- `approver`

Permissions are seeded via `database/seeders/RbacSeeder.php`.

## Setup

1. Configure `.env` for MySQL.
2. Install dependencies:
    - `composer install`
3. Generate app key:
    - `php artisan key:generate`
4. Run migrations:
    - `php artisan migrate`
5. Seed roles, permissions, and default users:
    - `php artisan db:seed`
6. Start queue worker:
    - `php artisan queue:work`
7. Start app:
    - `php artisan serve`

## Testing

- Run all tests:
    - `php artisan test`
- The automated test suite uses SQLite in-memory from `phpunit.xml`.

Default seeded admin:

- Email: `admin@academic.local`
- Password: `password`

## Workflow Lifecycle

1. Lecturer submits examination.
2. Service creates workflow instance + staged approvals.
3. `WorkflowSubmitted` event is dispatched.
4. Queued listeners send notifications to reviewers/approvers.
5. Reviewer/Approver records decision.
6. `WorkflowDecisionRecorded` event triggers queued notifications.

## Scalability Notes

- Business logic is queue-driver agnostic.
- Services avoid N+1 via eager loading.
- Domain schema uses normalized tables and foreign keys.
- Module boundaries are ready for extraction into packages later.
