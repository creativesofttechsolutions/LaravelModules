# Laravel Modules Package

A modular management package for Laravel, enabling seamless integration and structured development for Laravel-based projects.

---

## Features

* **Modular Structure**: Each module contains its own controllers, routes, views, configurations, and more.
* **Hooks System**: Supports actions and filters for customizable functionality.
* **Tenant Support**: Includes tenant-specific migrations and configurations for multitenancy.
* **Artisan Commands**: Simplified commands to create modules, controllers, middleware, models, and migrations.

---

## Module Structure

Modules are stored in the `modules/` directory and follow this organized structure:

```
modules/Blog/
├── Config/
│   ├── config.php
│   ├── navbar_config.php
│   └── hooks.php        # Example filters & actions
├── Controllers/
│   └── BlogController.php
├── Middleware/
├── Migrations/
├── Providers/
│   └── BlogServiceProvider.php  # Auto-loads views, routes, hooks
├── Resources/
│   └── views/
│       └── index.blade.php
├── Routes/
│   ├── web.php          # Web routes
│   ├── central.php      # Central routes
│   └── tenant.php       # Tenant routes
```

---

## Installation

Install the package via Composer:

```bash
composer require creativesofttechsolutions/laravelmodules
```

Publish configuration files:

```bash
php artisan vendor:publish --tag=modules-config
```

---

## Usage

### Create a New Module

Create a module using the Artisan command:

```bash
php artisan make:module Blog
```

### Create a New Controller

Add a controller to a module:

```bash
php artisan module:make-controller Blog TestController
```

### Create Middleware

Generate middleware for a module:

```bash
php artisan module:make-middleware Blog TestMiddleware
```

Register middleware in the module's service provider:

```php
$router = $this->app['router'];
$router->aliasMiddleware('example.middleware', ExampleMiddleware::class);
```

### Create a Model

Add a model to a module with an associated migration:

```bash
php artisan module:make-model Blog Post -m
```

### Create a Migration

Generate a migration for a module:

```bash
php artisan module:make-migration Blog create_posts_table
```

---

## Tenant-Specific Features

### Run Tenant Migrations

```bash
php artisan tenant:migrate --domain=demo.mlmmultitenancy.test --fresh
```

### Run Tenant Migrations with Seeders

```bash
php artisan tenant:migrate --domain=demo.mlmmultitenancy.test --fresh --seed
```

---

## Configuration

### Hooks System

Customize functionality with `hooks.php`:

```php
add_action('example_action', function ($args) {
    // Perform your action here
});

apply_filters('example_filter', $data);
```

### Auto-Loading

The `BlogServiceProvider` automatically registers:

* Routes
* Views
* Configurations
* Hooks

---

## Contribution

Contributions are welcome! Feel free to submit issues or pull requests to improve this package.

---

## License

This package is open-source software licensed under the [MIT license](LICENSE).
