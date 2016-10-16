<?php

namespace LukePOLO\LaraCart;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use LukePOLO\LaraCart\Contracts\LaraCartContract;
use LukePOLO\LaraCart\Traits\CartCoupons;
use LukePOLO\LaraCart\Traits\CartFees;
use LukePOLO\LaraCart\Traits\CartHelpers;
use LukePOLO\LaraCart\Traits\CartItems;
use LukePOLO\LaraCart\Traits\CartTotals;

/**
 * Class LaraCart.
 */
class LaraCart implements LaraCartContract
{
    use CartCoupons, CartFees, CartItems, CartTotals, CartHelpers;

    const SERVICE = 'laracart';

    protected $events;
    protected $session;
    protected $authManager;

    public $cart;
    public $prefix;
    public $itemModel;
    public $itemModelRelations;

    /**
     * LaraCart constructor.
     *
     * @param SessionManager $session
     * @param Dispatcher     $events
     * @param AuthManager    $authManager
     */
    public function __construct(SessionManager $session, Dispatcher $events, AuthManager $authManager)
    {
        $this->session = $session;
        $this->events = $events;
        $this->authManager = $authManager;
        $this->prefix = config('laracart.cache_prefix', 'laracart');

        $this->instance($this->session->get($this->prefix.'.instance', 'default'));
    }

    /**
     * Gets the instance in the session.
     *
     * @param string $instance
     *
     * @return $this cart instance
     */
    public function instance($instance = 'default')
    {
        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            if (!empty($cartSessionID = $this->authManager->user()->cart_session_id)) {
                $this->session->setId($cartSessionID);
                $this->session->start();
            }
        }

        $this->session->set($this->prefix.'.instance', $instance);

        $this->cart = $this->session->get($this->prefix.'.'.$instance);

        if (empty($this->cart)) {
            $this->cart = new Cart($instance);
            $this->session->push($this->prefix.'.instances', $instance);
            $this->events->fire('laracart.new');

            $this->session->set($this->prefix.'.'.$instance, $this->cart);
        }

        return $this;
    }

    /**
     * Gets all current instances inside the session.
     *
     * @return mixed
     */
    public function instances()
    {
        return $this->session->get($this->prefix.'.instances', []);
    }

    /**
     * Adds an Attribute to the cart.
     *
     * @param $attribute
     * @param $value
     */
    public function set($attribute, $value)
    {
        array_set($this->cart->attributes, $attribute, $value);

        $this->update();
    }

    /**
     * Gets an an attribute from the cart.
     *
     * @param $attribute
     * @param $defaultValue
     *
     * @return mixed
     */
    public function get($attribute, $defaultValue = null)
    {
        return array_get($this->cart->attributes, $attribute, $defaultValue);
    }

    /**
     * Gets all the carts attributes.
     *
     * @return mixed
     */
    public function attributes()
    {
        return $this->cart->attributes;
    }

    /**
     * Removes an attribute from the cart.
     *
     * @param $attribute
     * return void
     */
    public function remove($attribute)
    {
        array_forget($this->cart->attributes, $attribute);

        $this->update();
    }

    /**
     * Updates cart session
     * return void.
     */
    public function update()
    {
        $this->session->set($this->prefix.'.'.$this->cart->instance, $this->cart);

        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            $user = $this->authManager->user();
            $user->cart_session_id = $this->session->getId();
            $user->save();
        }

        $this->session->save();

        $this->events->fire('laracart.update', $this->cart);
    }

    /**
     * Get the count based on qty.
     *
     * @return int
     */
    public function count()
    {
        $count = 0;

        foreach ($this->items() as $item) {
            $count += $item->qty;
        }

        return $count;
    }

    /**
     * Find items in the cart matching a data set.
     *
     * @param $data array
     *
     * @return array
     */
    public function search(array $data)
    {
        $matches = [];

        foreach ($this->items() as $item) {
            if ($item->find($data)) {
                $matches[] = $item;
            }
        }

        return $matches;
    }

    /**
     * Empties the carts items
     * return void.
     */
    public function clear()
    {
        unset($this->cart->items);

        $this->update();

        $this->events->fire('laracart.empty', $this->cart->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     * return void.
     */
    public function destroy()
    {
        $instance = $this->cart->instance;

        $this->session->forget($this->prefix.'.'.$instance);
        $this->session->forget($this->prefix.'.instances', $instance);

        $this->instance('default');

        $this->events->fire('laracart.destroy', $instance);

        $this->update();
    }
}
