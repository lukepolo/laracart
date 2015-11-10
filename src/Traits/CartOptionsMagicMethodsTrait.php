<?php

namespace LukePOLO\LaraCart\Traits;

/**
 * Class CartOptionsMagicMethodsTrait
 *
 * @package LukePOLO\LaraCart\Traits
 */
trait CartOptionsMagicMethodsTrait
{
    public $options = [];

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
     * Magic Method allows for user input to set a value inside the options array
     *
     * @param $option
     * @param $value
     */
    public function __set($option, $value)
    {
        array_set($this->options, $option, $value);
    }

    /**
     * Magic Method allows for user to check if an option isset
     *
     * @param $option
     *
     * @return bool
     */
    public function __isset($option)
    {
        if (empty($this->options[$option]) === false) {
            return true;
        } else {
            return false;
        }
    }
}
