## LaraCart - Laravel Shopping Cart Package

[![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart)
[![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)

### Documentation

<a href="http://laracart.lukepolo.com/">http://laracart.lukepolo.com</a>

## Features

- Coupons
- Session Based System
- Cross Device Support
- Multiple cart instances
- Fees such as a delivery fee
- Taxation on a the item level
- Prices display currency and locale
- Endless item chaining for complex systems
- Totals of all items within the item chains
- Item Model Relation at a global and item level
- Quickly insert items with your own item models

## Installation

```bash
composer require lukepolo/laracart
```

Publish vendor config and migration:

```bash
php artisan vendor:publish --provider="LukePOLO\LaraCart\LaraCartServiceProvider
```
