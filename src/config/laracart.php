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
    | Calculate tax per item, rather than subtotal
    | https://github.com/lukepolo/laracart/issues/180
    |
    | This will vary , please investigate to follow the rules of your local laws
    | https://money.stackexchange.com/questions/15051/sales-tax-rounded-then-totaled-or-totaled-then-rounded
    |--------------------------------------------------------------------------
    |
    */
    'tax_by_item' => false,

    /*
    |--------------------------------------------------------------------------
    | Allows you to choose if the discounts applied are taxable
    |--------------------------------------------------------------------------
    |
    */
    'discountTaxable' => true,

    /*
    |--------------------------------------------------------------------------
    | Allows you to choose if the discounts applied to fees
    |--------------------------------------------------------------------------
    |
    */
    'discountOnFees' => false,

    /*
    |--------------------------------------------------------------------------
    | Allows you to configure if a user can apply multiple coupons
    |--------------------------------------------------------------------------
    |
    */
    'multiple_coupons' => false,

    /*
    |
    |                     **** DEPRECATED IN 1.3 ****
    |
    |--------------------------------------------------------------------------
    | Applied message when using getMessage on a coupon
    |--------------------------------------------------------------------------
    |
    */
    'coupon_applied_message' => 'Coupon Applied',

    /*
    |--------------------------------------------------------------------------
    | The default item model for your relations
    |--------------------------------------------------------------------------
    |
    */
    'item_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Binds your data into the correct spots for LaraCart
    |--------------------------------------------------------------------------
    |
    */
    'item_model_bindings' => [
        \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
        \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
        \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
        \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
        \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
            // put columns here for additional options,
            // these will be merged with options that are passed in
            // e.x
            // tax => .07
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The default item relations to the item_model
    |--------------------------------------------------------------------------
    |
    */
    'item_model_relations' => [],

    /*
    |--------------------------------------------------------------------------
    | This allows you to use multiple devices based on your logged in user
    |--------------------------------------------------------------------------
    |
    */
    'cross_devices' => false,

    /*
    |--------------------------------------------------------------------------
    | This allows you to use custom guard to get logged in user
    |--------------------------------------------------------------------------
    |
    */
    'guard' => null,
];
