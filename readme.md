## LaraCart - Laravel 5.1 Shopping Cart Package
[![Build Status](https://scrutinizer-ci.com/g/lukepolo/laracart/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lukepolo/laracart/build-status/master) [![Latest Stable Version](https://poser.pugx.org/lukepolo/laracart/v/stable)](https://packagist.org/packages/lukepolo/laracart) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lukepolo/laracart/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lukepolo/laracart/?branch=master)
[![Test Coverage](https://codeclimate.com/github/lukepolo/laracart/badges/coverage.svg)](https://codeclimate.com/github/lukepolo/laracart/coverage)
[![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart) [![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)


## Features
* Display Currency / Locale
* Easy Session Based Usage
* Taxation
* Multiple Cart Instances
* Coupons / Discounts
* Fees ex: delivery fees
* Price includes sub price attributes of items

## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    composer require lukepolo/laracart

Include Service Providers / Facade in `app/config/app.php`:

```php
	LukePOLO\LaraCart\LaraCartServiceProvider::class,
```

Include the Facade :

```php
	'LaraCart' => LukePOLO\LaraCart\Facades\LaraCart::class,
```

Copy over the configuration file by running the command :

```
    php artisan vendor:publish
```

Look through the configuration options and change as needed

## Overview

* [Usage](#usage)
* [Instances](#instances)
* [Coupons](#coupons)
* [Fees](#fees)
* [Exceptions](#exceptions)
* [Events](#events)


## Usage


**Adding an Item to the cart**

```php
    // First way we can just add like this
    LaraCart::add(1, 'Burger', 5, 2.00, [
        // Notice this is an array of arrays,
        // this allows us to further expand the cart functions to the options
        [
            'Description' => 'Bacon',
            'Price' => 1.00
        ]
    ]);

    // You can also do simple arrays for convenience
    LaraCart::add(2, 'Shirt', 200, 15.99, [
        'Size' => 'XL'
    ]);

    // If you need line items rather than just updating the qty you can do
    LaraCart::addLine(2, 'Shirt', 200, 15.99, [
        'Size' => 'XL'
    ]);
```

**Cart Attributes**

```php
    // Sometimes you want to give a cart some kind of attributes , such as labels
    LaraCart::addAttribute('label', 'Luke's Cart');
    LaraCart::updateAttribute('label', 'Not Luke's Cart');
    LaraCart::removeAttribute('label');

    // Gets all the attributes
    LaraCart::getAttributes();

```

**Updating an Items Attributes**

```php
    LaraCart::updateItem($itemHash, 'name', 'CheeseBurger w/Bacon');
    LaraCart::updateItem($itemHash, 'qty', 5);
    LaraCart::updateItem($itemHash, 'price', '2.50');
```

**Removing an item**

```php
    LaraCart::removeItem($itemHash);
```

**Empty / Destroying the Cart**

```php
    // Empty will only empty the contents
    LaraCart::emptyCart()

    // Destroy will remove the entire instance of the cart including coupons ect.
    LaraCart::destroyCart()
```

**Get the contents of the cart**

```php
    LaraCart::getItems();
    LaraCart::findItem($itemHash);
```

**Gets the total number of items in the cart**

```php
    LaraCart::count();
```

**Display Item Price with Locale**

```php
    // $tax = false by default
    $cartItem->getPrice($tax); // $24.23 | USD 24.23 depending on your settings
```

**Get the subtotal of the item**

```php
    // $tax = false by default
    $cartItem->subTotal($tax);

    // Gets the totals for the item options if applicable
    $cartItem->optionsTotal($formatMoney = true);
```

**Adding SubItems**

The reasoning behind sub items is to allow you add addiontal items without the all the nesscary thigns that a regular item needs. For instance if you really wanted the same item but in a differnt size  and that size costs more, you can add it as a subitem so it caculates in the price.

```php
    $cartItem->addSubItem([
        'Description' => 'Fries',
        'Price' => '.75'
    ]);

    // To update you can do on the item
    $cartItem->findSubItem($itemHash)->update('price') = 1.00;
```

**Get the Sub-Total of the cart**

This also includes the prices in the sub items and attributes

```php
    LaraCart::subTotal($tax = false, $formatted = true);
    LaraCart::getTotalDiscount($formatted = false);
    LaraCart::taxTotal($formatted = false);
    LaraCart::total($formatted = false, $withDiscount = true);
```


## Instances
Instances is a way that we can use multiple carts within the same session. By using:

```php
    LaraCart::setInstance('yourInstanceName');
```
Will switch to that instance of the cart. Each following request reuse the last instance of the cart set

## Coupons
Adding coupons could never be easier, currenlty there are a set of coupons inside LaraCart. To create new types of coupons just create a copy of one of the existing coupons and modifiy it!

```php
$coupon = new \LukePOLO\LaraCart\Coupons\Fixed($coupon->CouponCode, $coupon->CouponValue, [
    'description' => $coupon->Description
]);

LaraCart::addCoupon($coupon);

// To remove
LaraCart::removeCoupon($code);

// Couppons themeslves also have nifty formatting options , for instance Fixed value coupons can have a money format
$fixedCoupon->getValue(); // $2.50
$percentCoupon->getValue; // 15%
```

## Fees

Fees allow you to add extra charges to the cart for various reasons ex: delivery fees.

```php

LaraCart::addFee('deliveryFee', 5, $taxable =  false, $options = []);
LaraCart::removeFee('deliveryFee');

```

## Exceptions
LaraCart packages can throw the following exceptions:

| Exception                             | Reason                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *InvalidPrice*       | When trying to give an item a non currency format   |
| *InvalidQuantity*    | When trying to give an item a non-integer for a quantity  |
| *CouponException*    | When a coupon either is expired or an invalid amount |

## Events

The cart also has events build in:

| Event                | Fired                                   |
| -------------------- | --------------------------------------- |
| laracart.new      | When a new cart is started |
| laracart.update     | When a the cart is updated to the session |
| laracart.addItem($cartItem)      | When a item is added to the cart|
| laracart.updateItem($cartItem)      | When a item is updated|
| laracart.updateHash($cartItem)      | When a item hash is updated|
| laracart.removeItem($itemHash)      | When a item is removed from the cart |
| laracart.empty($cartInstance)      | When a cart is emptied |
| laracart.destroy($cartInstance)      | When a cart is destroyed |


License
----
MIT
