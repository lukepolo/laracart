<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\LaraCartInterface as LaraCartService;

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

    public $id;

    public $name;
    public $qty;
    public $price;
    public $options = [];

    public $tax;

    public $locale;
    public $internationalFormat;

    /**
     * @param $id
     * @param $name
     * @param $qty
     * @param $price
     * @param array $options
     */
    public function __construct($id, $name, $qty, $price, $options = [], LaraCartService $laraCartService)
    {
        $this->laraCartService = $laraCartService;

        $this->id = $id;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = (float) $price;

        // Sets the tax
        $this->tax = config('laracart.tax');

        // Sets the locale for the item
        $this->locale = config('laracart.locale', 'en_US');
        $this->internationalFormat = config('laracart.international_format');

        // Allows for simple options that are not arrays
        if(empty($options) === false) {
            if (is_array($options) === true) {
                $this->addOption($options);
            } else {
                // Generates all the options for the cart item
                foreach ($options as $option) {
                    $this->addOption($option);
                }
            }
        }

        // generate itemHash
        $this->generateHash();
    }

    /**
     * Generates a hash based on the cartItem array
     *
     * @return string itemHash
     */
    public function generateHash()
    {
        // Reset the itemHash to null
        $this->itemHash = null;

        // Transform into an array
        $cartItemArray = (array) $this;

        // Sort the options so we can get an accurate MD5
        if(empty($cartItemArray['options']) === false) {
            ksort($cartItemArray['options']);
        }

        // Create an md5 out of the array
        $this->itemHash = $itemHash = md5(json_encode($cartItemArray));

        return $itemHash;
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
     * @param array $option
     */
    public function addOption(array $option)
    {
        $cartItemOption = new CartItemOption($option);

        $this->options[] = $cartItemOption;

        $this->generateHash();
    }

    /**
     * Finds an items option by its key and value
     *
     * @param $updateByKey
     * @param $keyValue
     *
     * @return mixed
     */
    public function findOption($updateByKey, $keyValue)
    {
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
        // Initial  price of the item
        $price = $this->price;

        // Check to see if any of the sub options have a price associated with it
        foreach($this->options as $option) {
            $price += $option->price;
        }

        // add tax to the item
        if($tax) {
            $price += $price * $this->tax;
        }

        // Formats the price based on the locale
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
     */
    public function update($key, $value)
    {
        switch($key) {
            case 'qty' :
                // validate qty
                if(is_int($value) === false) {
                    throw new InvalidQuantity();
                }
            break;
            case 'price' :
                // validate is currency
                if(is_numeric($value) === false || preg_match('/\.(\d){3}/', $value)) {
                    throw new InvalidPrice();
                }
            break;
        }

        if(isset($this->$key) === true) {
            $this->$key = $value;
        } else {
            throw new UnknownItemProperty();
        }

        $this->generateHash();
    }

    /**
     * Updates an items option by a key value pair
     *
     * @param $keyValue - the value that is used to search for a specific option
     * @param $updateKey - the key that you wish to update
     * @param $updateValue - the value to replace inside the key
     * @param string $updateByKey - the key that it searches for to find the option
     *
     * @throws InvalidOption
     */
    public function updateOption($keyValue, $updateKey, $updateValue, $updateByKey = 'id')
    {
        $option = $this->findOption($updateByKey, $keyValue);

        if(empty($option) === false) {
            $option->update($updateKey, $updateValue);
        } else {
            throw new InvalidOption();
        }

        $this->generateHash();
    }


    /**
     * Removes an items option by a key value pair
     *
     * @param $keyValue - the value that is used to search for a specific option
     * @param string $removeByKey - the key that it searches for to find the option
     *
     * @throws InvalidOption
     */
    public function removeOption($keyValue, $removeByKey = 'id')
    {
        $this->options = array_values(
            collect($this->options)
                ->keyBy($removeByKey)
                ->forget($keyValue)
                ->toArray()
        );

        $this->generateHash();
    }

    /**
     * Updates all options for an item
     * @param $options
     */
    public function updateOptions($options)
    {
        $this->options = [];

        if(empty($options) === false) {
            // Generates all the options for the cart item
            foreach($options as $option) {
                $this->addOption($option);
            }
        }

        $this->generateHash();
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
        // Formats the total based on the locale
        if($format) {
            return $this->laraCartService->formatMoney($this->getPrice($tax, false) * $this->qty, $this->locale, $this->internationalFormat);
        } else {
            return $this->getPrice($tax, false) * $this->qty;
        }
    }
}