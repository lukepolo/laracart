<?php

namespace LukePOLO\LaraCart\Traits;

trait CouponTrait
{
    public $attributes = [];

    /**
     * Magic Method allows for user input as an object
     *
     * @param $attribute
     *
     * @return mixed | null
     */
    public function __get($attribute)
    {
        return array_get($this->attributes, $attribute);
    }

    /**
     * Magic Method allows for user input to set a value inside a object
     *
     * @param $attribute
     * @param $value
     */
    public function __set($attribute, $value)
    {
        array_set($this->attributes, $attribute, $value);
    }

    /**
     * Magic Method allows for user to check if an option isset
     *
     * @param $attribute
     *
     * @return bool
     */
    public function __isset($attribute)
    {
        if(empty($this->attributes[$attribute]) === false) {
            return true;
        } else {
            return false;
        }
    }

}
