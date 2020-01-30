## LaraCart - Laravel Shopping Cart Package (<a href="http://laracart.lukepolo.com/">http://laracart.lukepolo.com</a>)

[![Build Status](https://travis-ci.org/lukepolo/laracart.svg?branch=master)](https://travis-ci.org/lukepolo/laracart) [![Latest Stable Version](https://poser.pugx.org/lukepolo/laracart/v/stable)](https://packagist.org/packages/lukepolo/laracart) [![Test Coverage](https://codeclimate.com/github/lukepolo/laracart/badges/coverage.svg)](https://codeclimate.com/github/lukepolo/laracart/coverage) [![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart) [![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)

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

## Laravel compatibility

| Laravel           | laracart   |
| :---------------- | :--------- |
| 5.1 \| 5.2 \| 5.3 | 1.1 \| 1.2 |
| 5.4+              | 1.3+       |

## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    {
        "require": {
            ........,
            "lukepolo/laracart": "1.8.*"
        }
    }

If using 5.4 you will need to include the service providers / facade in `app/config/app.php`:

```php
	LukePOLO\LaraCart\LaraCartServiceProvider::class,
```

Include the Facade :

```php
	'LaraCart' => LukePOLO\LaraCart\Facades\LaraCart::class,
```

Copy over the configuration file by running the command:

```
    php artisan vendor:publish --provider='LukePOLO\LaraCart\LaraCartServiceProvider'
```

### Documentation

<a href="http://laracart.lukepolo.com/">http://laracart.lukepolo.com</a>

To Contribute to documentation use this repo :

https://github.com/lukepolo/laracart-docs

## License

MIT
