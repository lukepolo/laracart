# Getting Started
 
## Overview
LaraCart was built to be a easy , fast , and simple solution for cart based systems.

What makes LaraCart unique is it what it includes out of the box :

- Coupons
- Session Based System
- Cross Device Support
- Multiple cart instances
- Fees such as a delivery fee
- Taxation on a the item level
- Endless item chaining for complex systems
- Totals of all items within the item chains
- Item Model Relation at a global and item level
- Quickly insert items with your own item models

## Installation
Edit your project's composer.json file by adding:

    {
        "require": {
            ...
            "lukepolo/laracart": "1.1.*"
        }
    }

Include Service Providers / Facade in app/config/app.php :

    LukePOLO\LaraCart\LaraCartServiceProvider::class

Include the Facade :

    'LaraCart' => LukePOLO\LaraCart\Facades\LaraCart::class

Publish vendor config and migration :

    php artisan vendor:publish --provider='LukePOLO\LaraCart\LaraCartServiceProvider'
    
Look through the configuration options and change as needed.

##Configuration
LaraCart has a lot of configuration options, please make sure you run :

    php artisan vendor:publish --provider='LukePOLO\LaraCart\LaraCartServiceProvider'
    
After please make sure you go through and customize for your needs
