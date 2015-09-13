<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Contracts\LaraCartContract;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartContract
{
    public $cart;

    /**
     * @param LaraCartContract $laraCartService | LukePOLO\LaraCart\LaraCart $laraCartService
     */
    function __construct()
    {
        $this->session = app('session');
        $this->events = app('events');
        $this->setInstance($this->session->get('laracart.instance', 'default'));
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     *
     * @param string $instance
     */
    public function setInstance($instance = 'default')
    {
        $this->instance = $instance;

        $this->get($instance);

        // set in the session that we are using a different instance
        $this->session->set('laracart.instance', $instance);

        $this->events->fire('laracart.new');
    }

    /**
     * Gets the instance in the session
     *
     * @param string $instance
     *
     * @return $this cart instance
     */
    public function get($instance = 'default')
    {
        if(empty($this->cart = $this->session->get(config('laracart.cache_prefix', 'laracart.').$instance))) {
            $this->cart = new Cart($instance);
        }

        return $this->cart;
    }

    /**
     * Formats the number into a money format based on the locale and international formats
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     *
     * @return string
     */
    public function formatMoney($number, $locale = null, $internationalFormat = null)
    {
        if (empty($locale) === true) {
            $locale = config('laracart.locale', 'en_US');
        }

        if (empty($internationalFormat) === true) {
            $internationalFormat = config('laracart.international_format');
        }

        setlocale(LC_MONETARY, $locale);
        if ($internationalFormat) {
            return money_format('%i', $number);
        } else {
            return money_format('%n', $number);
        }
    }

    /**
     * Generates a hash for an object
     *
     * @param $object
     * @return string
     */
    public function generateHash($object)
    {
        return md5(json_encode($object));
    }

    /**
     * Generates a random hash
     *
     * @return string
     */
    public function generateRandomHash()
    {
        return str_random(40);
    }

    public function __call($method, $args)
    {
        if (!method_exists($this->cart, $method)) {
            throw new \Exception("unknown method [$method]");
        }

        return call_user_func_array([
            $this->cart,
            $method
        ], $args);
    }
}