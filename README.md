# Ominimo Test: Simple Blog

A simple blog for the Ominimo BE developer assignment, built with Laravel 13, React (Inertia), Tailwind CSS and SQLite.

## Run it locally

You need PHP 8.3+, Composer and Node 22.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed     # answer "yes" when asked to create the database
composer dev
```

Open http://localhost:8000.

## Run it with Docker

**Development** (hot reload):

```bash
docker compose up --build
```

Open http://localhost:8000.

**Production** (no sample data):

```bash
cp .env.docker.example .env.docker
php artisan key:generate --show     # paste the output after APP_KEY= in .env.docker
docker compose up --build prod
```

Open http://localhost:8080.

## Demo accounts

The sample data has 6 users, 18 posts and 180 comments. Every password is `password`.

| Email | Role |
| --- | --- |
| `mihailobabic@example.com` | admin (can delete any post or comment) |
| `user@example.com` | user |

## Tests

```bash
php artisan test
```

Or in Docker:

```bash
docker compose run --rm test
```

## Notes

- **Who can do what**

  | Action | Guest | User | Admin |
  | --- | --- | --- | --- |
  | Read posts and comments | yes | yes | yes |
  | Create a post | no | yes | yes |
  | Edit a post | no | own posts | own posts |
  | Delete a post | no | own posts | any post |
  | Comment | yes | yes | yes |
  | Delete a comment | no | own comments, and any comment on own posts | any comment |

- Admins can remove any content but can't edit someone else's post, so a post never shows text its author didn't write.
- Guest comments have no user and are shown as "Guest". Deleting a user deletes their posts (with the comments on them), but keeps their comments on other posts as guest comments.
- Routes that need a logged in user use Laravel's built-in `auth` middleware, it already does exactly what's needed so no custom one was written. Owner/admin rules live in `PostPolicy` and `CommentPolicy`.
- Validation is in Form Requests (`StorePostRequest`, `UpdatePostRequest`, `StoreCommentRequest`).
- The frontend is React through Inertia (the Breeze React stack): controllers pass data straight to the React pages instead of going through a separate JSON API.
