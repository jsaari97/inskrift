# Inskrift

Inskrift is a small, modern, and privacy-friendly guestbook plugin for WordPress.

The project requires PHP 8.4 or later, WordPress 6.7 or later, Node.js 24 or
later, Composer, and Docker.

## Set up the project

```sh
composer install
npm install
npm run build
npm run env:start
```

The local WordPress site is at <http://localhost:8888>. Use `admin` as the user
name and `password` as the password.

## Run the checks

```sh
composer check
npm run check
npm run test:php
```

Use `npm run env:start:minimum` and `npm run test:php:minimum` to test with
WordPress 6.7 and PHP 8.4. The minimum-version site is at
<http://localhost:8890>.
