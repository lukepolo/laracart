<?php namespace LukePOLO\LaraCart;

/**
 * Class CartItemOption
 *
 * @package LukePOLO\LaraCart
 */
class CartItemOption
{
    public $id;
    public $options;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->id = md5(json_encode($options));
        $this->options = $options;
    }

    /**
     * Updates an option by its key
     *
     * @param $key
     * @param $value
     */
    public function update($key, $value)
    {
        $this->$key = $value;
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

    /**
     * Magic Method allows for user input to set a value inside a object
     *
     * @param $option
     * @param $value
     */
    public function __set($option, $value)
    {
        array_set($this->options, $option, $value);
    }
}