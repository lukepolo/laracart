<?php namespace LukePOLO\LaraCart;

class CartItemOption
{
    public function __construct($options)
    {
        $this->options = $options;
    }

    public function __get($option)
    {
        return array_get($this->options, $option);
    }

    public function search($option)
    {
        return array_get($this->options, $option);
    }
}