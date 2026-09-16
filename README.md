# Laravel Inventory Manager

A simple inventory application with product creation, editing, JSON storage,
and automatic total calculations.

## Requirements

- PHP 8.2 or newer with SQLite support
- Composer
- Node.js and npm
- Internet connection for the Bootstrap stylesheet

## Run the Project

Extract the ZIP and open a terminal in the project folder.

For the first setup, run:

```bash
composer run setup
```

Then start the application:

```bash
php artisan serve
```

Open http://127.0.0.1:8000 in your browser.

For later runs, only `php artisan serve` is needed.

## Product Data

Products are saved in `storage/app/products.json`. This file is created
when the first product is added.

The `storage` and `bootstrap/cache` folders must be writable.

## Repository

https://github.com/Basit95/laravel-skills-test