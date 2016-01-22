## LaraCart - Laravel Shopping Cart Package
[![Build Status](https://travis-ci.org/lukepolo/laracart.svg?branch=master)](https://travis-ci.org/lukepolo/laracart) 
[![Latest Stable Version](https://poser.pugx.org/lukepolo/laracart/v/stable)](https://packagist.org/packages/lukepolo/laracart)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lukepolo/laracart/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lukepolo/laracart/?branch=master)
[![Test Coverage](https://codeclimate.com/github/lukepolo/laracart/badges/coverage.svg)](https://codeclimate.com/github/lukepolo/laracart/coverage)
[![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart) [![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)

## Features
* Coupons
* Session Based System
* Multiple cart instances
* Fees  such as a delivery fee
* Taxation on a the item level
* Prices display currency and locale
* Endless item chaining for complex systems
* Totals of all items within the item chains

## Laravel compatibility

 Laravel  | laracart
:---------|:----------
 5.x      | 1.x


## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    {
	    "require": {
	        ........,
	        "lukepolo/laracart": "1.0.*"
	    }
    }

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
* [SubItems](#subitems)
* [Currency & Locale](#currency--locale)
* [Coupons](#coupons)
* [Fees](#fees)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)

## Usage

**Adding Items**

```php
    // Adding an item to the cart
    LaraCart::add(2, 'Shirt', 200, 15.99, [
        'size' => 'XL'
    ]);

    // If you need line items rather than just updating the qty you can do
    LaraCart::addLine(2, 'Shirt', 200, 15.99, [
        'size' => 'XL'
    ]);
    
    // Also you can have your item not taxed
    $item = LaraCart::addLine(2, 'Shirt', 200, 15.99, [
        'size' => 'XL'
        ]
        $taxable = false
    );
```

**Item Hashes**

```php
    $item = LaraCart::add(2, 'Shirt', 200, 15.99, [
         'size' => 'XL'
    ]);
     
    $itemHash = $item->getHash();
   
    // That way we can find / remove from the cart
    LaraCart::findItem($itemHash);
    LaraCart::removeItem($item->getHash());
```

**Cart Items**

```php
    LaraCart::getItems();
    LaraCart::count($withItemQty = true); // If set to false it will ignore the qty on the items and get the line count
        
    LaraCart::updateItem($itemHash, 'name', 'CheeseBurger w/Bacon');
    LaraCart::updateItem($itemHash, 'qty', 5);
    LaraCart::updateItem($itemHash, 'price', 2.50);
    LaraCart::updateItem($itemHash, 'tax', .045);
    
    // Or if you have the item object already
    $item = LaraCart::add(2, 'Shirt', 200, 15.99, [
        'size' => 'XL'
    ]);
    
    $item->size = 'L';
    
    $item->price($tax = false, $formatted = true); // $4.50 | USD 4.50
    
```

**Cart Attributes**

```php
    LaraCart::addAttribute('label', "Luke's Cart");
    LaraCart::updateAttribute('label', "Not Luke's Cart");
    LaraCart::removeAttribute('label');

    // Gets all the attributes
    LaraCart::getAttributes();
```

**Emptying / Destroying the Cart**

```php
    // Empty will only empty the contents
    LaraCart::emptyCart()

    // Destroy will remove the entire instance of the cart including coupons / fees etc.
    LaraCart::destroyCart()
```

**Cart Totals**

```php
    LaraCart::subTotal($tax = false, $format = true, $withDiscount = true);
    LaraCart::totalDiscount($formatted = false);
    LaraCart::taxTotal($formatted = false);
    LaraCart::total($formatted = false, $withDiscount = true);
```

## SubItems
The reasoning behind sub items is to allow you add additional items without the all the necessary things that a regular item needs. For instance if you really wanted the same item but in a different size and that size costs more, you can add it as a sub item so it calculates in the price.

```php
    $item = \LaraCart::add(2, 'Shirt', 1, 15.99, [
        'size' => 'XXL'
    ]);
    
    $item->addSubItem([
        'description' => 'Extra Cloth Cost', // this line is not required!
        'price' => 3.00
    ]);
    
    $item->subTotal($tax = false); // $18.99
    $item->subItemsTotal($tax = false, $formatMoney = true); // $3.00
```

## Currency & Locale
Laracart comes built in with a currency / locale display. To configure just checkout the config.php. You can set to show the locale (USD) or the currency ($)

```php
    $item->price($tax = false, $formatted = true); // $4.50 | USD 4.50
    
    LaraCart::total() // $24.23 | USD 24.23
```

## Coupons
Adding coupons could never be easier, currently there are a set of coupons inside LaraCart. To create new types of coupons just create a copy of one of the existing coupons and modify it!

```php
    $coupon = new \LukePOLO\LaraCart\Coupons\Fixed($coupon->CouponCode, $coupon->CouponValue, [
        'description' => $coupon->Description
    ]);
    
    LaraCart::addCoupon($coupon);
    LaraCart::removeCoupon($code);
    
    $fixedCoupon->getValue(); // $2.50
    $percentCoupon->getValue; // 15%
```

## Fees
Fees allow you to add extra charges to the cart for various reasons ex: delivery fees.

```php
    LaraCart::addFee('deliveryFee', 5, $taxable =  false, $options = []);
    LaraCart::removeFee('deliveryFee');
```

## Instances
Instances is a way that we can use multiple carts within the same session. Each following request reuse the last instance of the cart set

```php
    LaraCart::setInstance('yourInstanceName');
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
| laracart.addItem($item)      | When a item is added to the cart|
| laracart.updateItem($item)      | When a item is updated|
| laracart.removeItem($itemHash)      | When a item is removed from the cart |
| laracart.empty($cartInstance)      | When a cart is emptied |
| laracart.destroy($cartInstance)      | When a cart is destroyed |

License
----
MIT
