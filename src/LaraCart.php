<?php

namespace LukePOLO\LaraCart;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use LukePOLO\LaraCart\Contracts\LaraCartContract;
use LukePOLO\LaraCart\Traits\CartCoupons;
use LukePOLO\LaraCart\Traits\CartFees;
use LukePOLO\LaraCart\Traits\CartItems;
use LukePOLO\LaraCart\Traits\CartTotals;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartContract
{
    use CartCoupons, CartFees, CartItems, CartTotals;
    const SERVICE = 'laracart';
    const HASH = 'generateCartHash';
    const RANHASH = 'generateRandomCartItemHash';

    protected $events;
    protected $session;
    protected $authManager;

    public $cart;
    public $prefix;
    public $itemModel;
    public $itemModelRelations;

    /**
     * // TODO - this really should just be a helper
     * Formats the number into a money format based on the locale and international formats
     * @param $number
     * @param $locale
     * @param $internationalFormat
     * @param $format
     * @return string
     */
    public static function formatMoney($number, $locale = null, $internationalFormat = null, $format = true)
    {
        $number = number_format($number, 2, '.', '');

        if ($format) {
            setlocale(LC_MONETARY, null);
            setlocale(LC_MONETARY, empty($locale) ? config('laracart.locale', 'en_US.UTF-8') : $locale);

            if (empty($internationalFormat) === true) {
                $internationalFormat = config('laracart.international_format', false);
            }

            $number = money_format($internationalFormat ? '%i' : '%n', $number);
        }

        return $number;
    }

    /**
     * LaraCart constructor.
     * @param SessionManager $session
     * @param Dispatcher $events
     * @param AuthManager $authManager
     */
    public function __construct(SessionManager $session, Dispatcher $events, AuthManager $authManager)
    {
        $this->session = $session;
        $this->events = $events;
        $this->authManager = $authManager;
        $this->prefix = config('laracart.cache_prefix', 'laracart');
        $this->itemModel = config('laracart.item_model', null);
        $this->itemModelRelations = config('laracart.item_model_relations', []);

        $this->setInstance($this->session->get($this->prefix . '.instance', 'default'));
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     * @param string $instance
     * @return LaraCart
     */
    public function setInstance($instance = 'default')
    {
        $this->get($instance);

        $this->session->set($this->prefix . '.instance', $instance);

        if (!in_array($instance, $this->getInstances())) {
            $this->session->push($this->prefix . '.instances', $instance);
        }
        $this->events->fire('laracart.new');

        return $this;
    }

    /**
     * Gets the instance in the session
     * @param string $instance
     * @return $this cart instance
     */
    public function get($instance = 'default')
    {
        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            if (!empty($cartSessionID = $this->authManager->user()->cart_session_id)) {
                $this->session->setId($cartSessionID);
                $this->session->start();
            }
        }

        if (empty($this->cart = $this->session->get($this->prefix . '.' . $instance))) {
            $this->cart = new Cart($instance);
        }

        return $this;
    }

    /**
     * Gets all current instances inside the session
     * @return mixed
     */
    public function getInstances()
    {
        return $this->session->get($this->prefix . '.instances', []);
    }

    /**
     * Adds an Attribute to the cart
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value)
    {
        array_set($this->cart->attributes, $attribute, $value);

        $this->update();
    }

    /**
     * Gets an an attribute from the cart
     * @param $attribute
     * @param $defaultValue
     * @return mixed
     */
    public function getAttribute($attribute, $defaultValue = null)
    {
        return array_get($this->cart->attributes, $attribute, $defaultValue);
    }

    /**
     * Gets all the carts attributes
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->cart->attributes;
    }

    /**
     * Removes an attribute from the cart
     * @param $attribute
     * return void
     */
    public function removeAttribute($attribute)
    {
        array_forget($this->cart->attributes, $attribute);

        $this->update();
    }

    /**
     * Updates cart session
     * return void
     */
    public function update()
    {
        $this->session->set($this->prefix . '.' . $this->cart->instance, $this->cart);

        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            $this->authManager->user()->cart_session_id = $this->session->getId();
            $this->authManager->user()->save();
        }

        $this->session->save();

        $this->events->fire('laracart.update', $this->cart);
    }

    /**
     * Get the count based on qty, or number of unique items
     * @param bool $withItemQty
     * @return int
     */
    public function count($withItemQty = true)
    {
        $count = 0;

        foreach ($this->getItems() as $item) {
            if ($withItemQty) {
                $count += $item->qty;
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Find items in the cart matching a data set
     * param $data
     * @param $data
     * @return array
     */
    public function find($data)
    {
        $matches = [];

        foreach ($this->getItems() as $item) {
            if ($item->find($data)) {
                $matches[] = $item;
            }
        }

        return $matches;
    }

    /**
     * Empties the carts items
     * return void
     */
    public function emptyCart()
    {
        unset($this->cart->items);

        $this->update();

        $this->events->fire('laracart.empty', $this->cart->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     * return void
     */
    public function destroyCart()
    {
        $instance = $this->cart->instance;

        $this->session->forget($this->prefix . '.' . $instance);

        $this->setInstance('default');

        $this->events->fire('laracart.destroy', $instance);

        $this->update();
    }

}
