<?php

return [
    // Storage name in the session
    'cache_prefix' => 'laracart_',

    // TODO - control expires
    'cache_expire' => '-1',

    // Allows to change the locale of the currency of the money formatter
    'locale' => 'en_US',

    // Tto display the local such as USD or just $
    'display_locale' => false,

    // Default tax for the cart
    'tax' => '.07'
];
