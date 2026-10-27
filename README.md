# Store Order & Inventory Mini-System

## Overview

A REST API built with Laravel for managing customers, products, orders, and inventory. It supports order creation, stock validation, tax calculations, customer order history, low-stock reporting, and queued order confirmation processing.

## Technologies

* PHP
* Laravel
* MySQL
* REST API
* PHPUnit
* Laravel Queue

## Features

* Create orders for customers.
* Validate product stock before order creation.
* Calculate subtotal, tax, and grand total.
* Reduce stock after a successful order.
* Retrieve customer order history using email.
* Retrieve low-stock products using a configurable threshold.
* Process order confirmation through a queued job.
* Automated tests for order creation, insufficient stock, customer order history, and low-stock products.

## Installation

### 1. Install dependencies

```bash
composer install
```

### 2. Configure environment

Copy `.env.example` to `.env` if you do not already have an environment file.

Configure your MySQL database credentials in `.env`, then run:

```bash
php artisan key:generate
```

### 3. Set up the database

Create the project database in MySQL and configure its name and credentials in `.env`.

Run the migrations:

```bash
php artisan migrate --seed
```

Run this command only against a suitable development database. Check your seeders before running them against an existing database.

### 4. Start the application

```bash
php artisan serve --port=8001
```

Base URL: `http://127.0.0.1:8001`

## API Endpoints

| Method | Endpoint                               | Description                                            |
| ------ | -------------------------------------- | ------------------------------------------------------ |
| POST   | `/api/orders`                          | Create an order                                        |
| GET    | `/api/customers/{email}/orders`        | Retrieve a customer's order history                    |
| GET    | `/api/products/low-stock`              | Retrieve products below the configured stock threshold |
| GET    | `/api/products/low-stock?threshold=20` | Retrieve products with stock below 20                  |

## Queue Processing

Configure the queue connection in `.env`:

```env
QUEUE_CONNECTION=database
```

Ensure the required queue tables exist and have been migrated. Start the worker in a separate terminal:

```bash
php artisan queue:work
```

The current order confirmation job logs confirmation details; it does not send an actual email.

## Running Tests

Run the automated test suite:

```bash
php artisan test
```

The current suite contains tests for:

* Successful order creation.
* Insufficient stock validation.
* Customer order history.
* Low-stock product retrieval.

## Notes

* Configure a separate testing database before running automated tests.
* Do not commit `.env` or other files containing secrets.
* Check your environment configuration and migrations before setting up the project on another machine.
