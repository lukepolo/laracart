<?php namespace LukePOLO\LaraCart;

/**
 * Class CartItemOption
 *
 * @package LukePOLO\LaraCart
 */
class CartItemOption
{
    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Magic Method allows for user input as an object
     *
     * @param $option
     *
     * @return mixed | null
     */
    public function __get($option)
    {
        return array_get($this->options, $option);
    }
}