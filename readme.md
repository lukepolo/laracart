## LaraCart
[![Latest Stable Version](https://poser.pugx.org/lukepolo/laracart/v/stable)](https://packagist.org/packages/lukepolo/laracart) [![Total Downloads](https://poser.pugx.org/lukepolo/laracart/downloads)](https://packagist.org/packages/lukepolo/laracart) [![Latest Unstable Version](https://poser.pugx.org/lukepolo/laracart/v/unstable)](https://packagist.org/packages/lukepolo/laracart) [![License](https://poser.pugx.org/lukepolo/laracart/license)](https://packagist.org/packages/lukepolo/laracart)

## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

    composer require "lukepolo/laracart"


Include Service Providers / Facade in `app/config/app.php`:
```php
	LukePOLO\LaraCart\LaraCartServiceProvider::class,
```

Optionally include the Facade (suggested) :
```php
	'LaraCart' => LukePOLO\LaraCart\Facades\LaraCart::class,
```

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)
* [Example](#example)

## Usage

**LaraCart::add()**

```php
    /**
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options an array of options
    */
```

Example:
```php
    LaraCart::add(1, 'Burger', 5, 2.00, [
        [
            'Description' => 'Bacon',
            'Price' => 1.00
        ]
    ]);

```

## Instances

## Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                             | Reason                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *InvalidOption*       | When trying to update an option on an item, cannot find a key value pair that mathces  |
| *InvalidPrice*    | When trying to give an item a non currency format   |
| *InvalidQuantity*    | When trying to give an item a non-integer for a quantity  |
| *UnknownItemProperty*    | When trying to update an items attribute that doesn't exsist |

## Events

The cart also has events build in. There are five events available for you to listen for.

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



## Example

```


License
----
MIT