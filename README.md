# Laravel Inventory Manager

A Laravel application for managing product stock with a Bootstrap interface,
JSON file storage, and Ajax updates.

## Features

- Add products with a name, stock quantity, and price per item.
- Display products in ascending order of submission date and time.
- Calculate each product's total value as quantity multiplied by price.
- Display the combined value of all products in the final table row.
- Edit existing products without changing their original submission time.
- Load, create, and update records without reloading the page.
- Validate submitted data on the server.

## Technologies

- Laravel 12
- PHP
- Blade templates
- Bootstrap 5
- JavaScript Fetch API
- CSS
- JSON file storage

## Requirements

- 64-bit PHP 8.2 or newer with Laravel's required PHP extensions
- Composer
- Node.js and npm for the included frontend build tools
- SQLite support for Laravel's default database configuration
- Write access to `storage` and `bootstrap/cache`
- Internet access to load the Bootstrap stylesheet from its CDN

## Installation

Clone the repository:

```bash
git clone https://github.com/Basit95/laravel-skills-test.git
cd laravel-skills-test
```

Run the setup script:

```bash
composer run setup
```

The setup script installs dependencies, creates the environment file if
missing, generates an application key, runs the default database migrations,
and builds the frontend assets.

Use this command for a fresh installation. It generates a new application
key and should not be used as a routine update command on an existing deployment.

Start the development server:

```bash
php artisan serve
```

Open the local address printed in the terminal, normally:

```text
http://127.0.0.1:8000
```

The inventory page loads its custom CSS and JavaScript directly from `public`.
It does not require a running Vite development server.

## Usage

1. Enter the product name, quantity in stock, and price per item.
2. Select **Add product**.
3. The product appears in the table and the sum total updates.
4. Select **Edit** beside a product to populate the form.
5. Update the values and select **Save changes**, or select **Cancel edit**.

## Validation

- Product name: required, with a maximum of 120 characters.
- Quantity: a whole number between 0 and 100,000.
- Price: between 0 and 99,999.99, with up to two decimal places.
- Maximum inventory size: 1,000 products.

Prices are calculated using integer cents to avoid floating-point rounding
issues. No specific currency is assumed.

## Data Storage

Product records are stored in:

```text
storage/app/products.json
```

The file is created when the first product is saved. An empty inventory is
displayed if the file does not yet exist.

A separate lock file coordinates access during reads and writes. Updates
are written to a temporary file before replacing the JSON file.

Submission timestamps are stored in UTC and displayed in the browser's
local time zone. Editing a product preserves its original submission time.

Product storage does not use database tables. The included default Laravel
migrations support the application's standard configuration.

Local inventory records and environment credentials are not included in
the repository.

## Main Files

| File | Purpose |
| --- | --- |
| `routes/web.php` | Page and product routes |
| `app/Http/Controllers/ProductController.php` | Validation and total calculations |
| `app/Services/ProductStore.php` | JSON storage and file locking |
| `resources/views/inventory.blade.php` | Inventory form and table |
| `public/css/inventory.css` | Custom styles |
| `public/js/inventory.js` | Ajax requests and interface updates |

## Verification

To run the included Laravel tests:

```bash
php artisan test
```

The starter tests do not cover the inventory feature. Check the following
manually:

- Add and edit products without a full page reload.
- Refresh the page and confirm records remain available.
- Confirm invalid inputs display validation messages.
- Confirm records are ordered by submission time.
- Check the form and scrollable table on a narrow screen.

Example calculation:

| Product | Quantity | Price | Total |
| --- | ---: | ---: | ---: |
| Notebook | 7 | 0.20 | 1.40 |
| Desk lamp | 2 | 12.50 | 25.00 |
| **Sum total** | | | **26.40** |

## Deployment Notes

Configure the web server to serve the `public` directory and ensure the PHP
process can write to `storage` and `bootstrap/cache`.

The application has a shared inventory and does not include authentication
or user-specific access controls.

## Repository

https://github.com/Basit95/laravel-skills-test