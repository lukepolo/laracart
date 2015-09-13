## LaraCart - Laravel 5.1 Shopping Cart Package
[![Latest Stable Version](https://poser.pugx.org/lukepolo/laracart/v/stable)](https://packagist.org/packages/lukepolo/laracart) [![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart) [![Latest Unstable Version](https://poser.pugx.org/lukepolo/laracart/v/unstable)](https://packagist.org/packages/lukepolo/laracart) [![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)

## !!WARNING!! Currently In Development
There are features that are incomplete and others that are not fully tested, please feel free to submit issues and enhancements

## Features
* Display Currency along with Locale 
* Easy Session Base Usage
* Totals / SubTotals with taxes
* Items have locale and currency and tax separate from the cart
* Multiple Cart Instances
* Unique Item Hash that is updated after every update to the item
* Option can have prices and are calculated in the totals and sub totals

## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    composer require lukepolo/laracart

Include Service Providers / Facade in `app/config/app.php`:
```php
	LukePOLO\LaraCart\LaraCartServiceProvider::class,
```

Optionally include the Facade (suggested) :
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
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Usage

Note : Because of the item hashing you must be careful how you update your items.Each change to an item will update its hash either continue to use the same item object or make sure to use the hash that is returned.

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
```

**Find a specific item in the cart**
```php
    LaraCart::findItem($itemHash);
```
**Gets the total number of items in the cart**
```php
    LaraCart::count();
```

**Display Item Price with Locale**
```php
    // $tax = false by default
    $cartItem->getPrice($tax); // $24.23 | USD 24.23
```

**Get the subtotal of the item**
```php
    // $tax = false by default
    $cartItem->subTotal($tax);

    // Gets the totals for the item options if applicable
    $cartItem->optionsTotal($formatMoney = true);
```

**Adding SubItems**
```php
    $cartItem->addSubItem([
        'Description' => 'Fries',
        'Price' => '.75'
    ]);
    
    To update you can do on the item 
    $cartItem->findSubItem($itemHash)->update('price') = 1.00;
```

**Get the Sub-Total of the cart** (This also includes the prices in the sub items and attributes!)
```php
    LaraCart::subTotal($tax = false, $formatted = true);
```
**Get the total of the cart**
```php 
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
````
$coupon = new \LukePOLO\LaraCart\Coupons\Fixed($coupon->CouponCode, $coupon->CouponValue, [
    'description' => $coupon->Description
]);

LaraCart::applyCoupon($coupon);

// To remove
LaraCart::removeCoupon($code);

// Couppons themeslves also have nifty formatting options , for instance Fixed value coupons can have a money format
$fixedCoupon->getValue(); // $2.50
$percentCoupon->getValue; // 15% 
````

## Exceptions
LaraCart packages can throw the following exceptions:

| Exception                             | Reason                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *InvalidOption*       | When trying to update an option on an item, cannot find a key value pair that matches  |
| *InvalidPrice*    | When trying to give an item a non currency format   |
| *InvalidQuantity*    | When trying to give an item a non-integer for a quantity  |
| *UnknownItemProperty*    | When trying to update an items attribute that doesn't exists |

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