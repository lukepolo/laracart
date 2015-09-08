<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\InvalidOption;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\Exceptions\UnknownItemProperty;

/**
 * Class CartItem
 *
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    protected $itemHash;

    private $laraCartService;

    public $id;

    public $name;
    public $qty;
    public $price;
    public $options = [];
    public $subItems = [];
    public $tax;

    public $locale;
    public $internationalFormat;


    /**
     * @param string $id
     * @param string $name
     * @param int $qty
     * @param float $price
     * @param array $options
     * @param bool $lineItem
     */
    public function __construct($id, $name, $qty, $price, $options = [], $lineItem = false)
    {
        $this->setCartService();

        $this->id = $id;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = floatval($price);
        $this->lineItem = $lineItem;

        $this->tax = config('laracart.tax');

       $this->options = $options;

        $this->generateHash();
    }

    /**
     * Sets the LaraCart Services class into the instance
     */
    public function setCartService()
    {
        $this->laraCartService = \App::make(LaraCartInterface::class);
    }

    /**
     * TODO - move to laracart class
     *
     *  Generates a hash based on the cartItem array
     *
     * @param bool $force
     *
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        if($force === true) {
            $this->itemHash = null;
        }

        if($this->lineItem === false) {
            $this->itemHash = null;

            $cartItemArray = (array)$this;

            if (empty($cartItemArray['options']) === false) {
                ksort($cartItemArray['options']);
            }

            $this->itemHash = $itemHash = md5(json_encode($cartItemArray));
        } elseif(empty($this->itemHash) === true) {
            $this->itemHash = str_random(40);
        }
        return $this->itemHash;
    }

    /**
     * Gets the hash for the item
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Adds an option to a cart item
     *
     * @param array $subItem
     *
     * @return string $itemHash
     */
    public function addSubItem(array $subItem)
    {
        $subItem = new CartSubItem($subItem);

        $this->subItems[$subItem->getHash()] = $subItem;

        return $this->generateHash();
    }

    /**
     * Finds an items option by its key and value
     */
    public function findSubItem($itemHash)
    {
        dd('Find item by itemhash');
        return array_first($this->options, function($optionKey, $optionValue) use($updateByKey, $keyValue)
        {
            if($optionValue->$updateByKey == $keyValue) {
                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * Gets the price of the item with or without tax, with the proper format
     *
     * @param bool $tax
     * @param bool $format
     *
     * @return float|string
     */
    public function getPrice($tax = false, $format = true)
    {
        $price = $this->price;

        foreach($this->subItems as $subItem) {
            if(isset($subItem->price)) {
                dd('Price needs to be addded for sub item '.$subItem->pirce);
            }

            foreach($subItem->items as $item) {
                $price += $item->getPrice($tax, false);
            }
        }

        if($tax) {
            $price += $price * $this->tax;
        }

        if($format) {
            return $this->laraCartService->formatMoney($price, $this->locale, $this->internationalFormat);
        } else {
            return $price;
        }
    }

    /**
     * Updates an items properties
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidQuantity | InvalidPrice | UnknownItemProperty
     *
     * @return string $itemHash
     */
    public function update($key, $value)
    {
        switch($key) {
            case 'qty' :
                if(is_int($value) === false) {
                    throw new InvalidQuantity();
                }
            break;
            case 'price' :
                if(is_numeric($value) === false || preg_match('/\.(\d){3}/', $value)) {
                    throw new InvalidPrice();
                }
            break;
        }

        dd('use laravel functions');

        if(isset($this->$key) === true) {
            $this->$key = $value;
        } else {
            throw new UnknownItemProperty();
        }

        return $this->generateHash();
    }


    public function updateOption($key, $value)
    {
        $this->update('options.'.$key, $value);
    }

    public function removeOption($key)
    {
        Dump('Trying to remove  '.$key);
        dd('TODO - update option');
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     *
     * @param bool $tax
     * @param bool $format
     *
     * @return float|string
     */
    public function subTotal($tax = false, $format = true)
    {
        if($format) {
            $total = $this->getPrice($tax, false) + $this->subItemsTotal($tax, false);
            return $this->laraCartService->formatMoney($total * $this->qty, $this->locale, $this->internationalFormat);
        } else {
            return $this->getPrice($tax, false) * $this->qty;
        }
    }

    /**
     * Gets the totals for the options
     */
    public function subItemsTotal($tax = false, $format = true)
    {
        $total = 0;
        foreach($this->subItems as $item) {
            if(isset($item->price)) {
                $total += array_get($item->options, 'price');
            }
        }

        if($tax) {
            $total = $total + ($total * $this->tax);
        }

        if($format) {
            return $this->laraCartService->formatMoney($total, $this->locale, $this->internationalFormat);
        } else {
            return $total;
        }
    }
}
