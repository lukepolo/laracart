<?php

namespace LukePOLO\LaraCart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart\Facades
 */
class LaraCart extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laracart';
    }
}
