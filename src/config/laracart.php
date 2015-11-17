<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The caching prefix used to lookup the cart
    |--------------------------------------------------------------------------
    |
    */
    'cache_prefix' => 'laracart',

    /*
    |--------------------------------------------------------------------------
    | Locale is used to convert money into a readable format for the user,
    | please note the UTF-8 , helps to make sure its encoded correctly
    |
    | Common Locales
    |
    | English - United States (en_US): 123,456.00
    | English - UNITED KINGDOM (en_GB) 123,456.00
    | Spanish - Spain (es_ES): 123.456,000
    | Dutch - Netherlands (nl_NL): 123 456,00
    | German - Germany (de_DE): 123.456,00
    | French - France (fr_FR): 123 456,00
    | Italian - Italy (it_IT): 123.456,00
    |
    | This site is pretty useful : http://lh.2xlibre.net/locales/
    |
    |--------------------------------------------------------------------------
    |
    */
    'locale' => 'en_US.UTF-8',

    /*
    |--------------------------------------------------------------------------
    | If true displays the international format rather thant he national format
    |--------------------------------------------------------------------------
    |
    */
    'international_format' => false,

    /*
    |--------------------------------------------------------------------------
    | Sets the tax for the cart and items , you can change per item
    | via the object later if  needed
    |--------------------------------------------------------------------------
    |
    */
    'tax' => null,

    /*
    |--------------------------------------------------------------------------
    | Allows you to configure if a user can apply multiple coupons
    |--------------------------------------------------------------------------
    |
    */
    'multiple_coupons' => false
];
