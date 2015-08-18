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

        // Sets the tax
        $this->tax = config('laracart.tax');

        // Allows for simple options that are not arrays
        if(empty($options) === false) {
            // Generates all the options for the cart item
            foreach ($options as $optionKey => $option) {
                if (is_array($option)) {
                    $this->addOption($option);
                } else {
                    $this->addOption([
                        $optionKey => $option
                    ]);
                }
            }
        }

        // generate itemHash
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
     * Generates a hash based on the cartItem array
     *
     * @param bool $force
     *
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        // Forces a rehash
        if($force === true)
        {
            $this->itemHash = null;
        }

        // Line items never change their itemHash so we don't want to generate a new one
        if($this->lineItem === false) {
            // Reset the itemHash to null
            $this->itemHash = null;

            // Transform into an array
            $cartItemArray = (array)$this;

            // Sort the options so we can get an accurate MD5
            if (empty($cartItemArray['options']) === false) {
                ksort($cartItemArray['options']);
            }

            // Create an md5 out of the array
            $this->itemHash = $itemHash = md5(json_encode($cartItemArray));
        } elseif(empty($this->itemHash) === true) {
            // Generate a random string for the a line item
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
     * @param array $option
     *
     * @return string $itemHash
     */
    public function addOption(array $option)
    {
        $cartItemOption = new CartItemOption($option);

        $this->options[] = $cartItemOption;

        return $this->generateHash();
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
     *
     * @return string $itemHash
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

        return $this->generateHash();
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
     *
     * @return string $itemHash
     */
    public function updateOption($keyValue, $updateKey, $updateValue, $updateByKey = 'id')
    {
        $option = $this->findOption($updateByKey, $keyValue);

        if(empty($option) === false) {
            $option->update($updateKey, $updateValue);
        } else {
            throw new InvalidOption();
        }

        return $this->generateHash();
    }


    /**
     * Removes an items option by a key value pair
     *
     * @param $keyValue - the value that is used to search for a specific option
     * @param string $removeByKey - the key that it searches for to find the option
     *
     * @throws InvalidOption
     *
     * @return string $itemHash
     */
    public function removeOption($keyValue, $removeByKey = 'id')
    {
        $this->options = array_values(
            collect($this->options)
                ->keyBy($removeByKey)
                ->forget($keyValue)
                ->toArray()
        );

        return $this->generateHash();
    }

    /**
     * Updates all options for an item
     *
     * @param $options
     *
     * @return string $itemHash
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

        return $this->generateHash();
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
            $total = $this->getPrice($tax, false) + $this->optionsTotal($tax, false);
            return $this->laraCartService->formatMoney($total * $this->qty, $this->locale, $this->internationalFormat);
        } else {
            return $this->getPrice($tax, false) * $this->qty;
        }
    }

    /**
     * Gets the totals for the options
     *
     * @param bool $format
     *
     * @return int|mixed
     */
    public function optionsTotal($tax = false, $format = true)
    {
        $total = 0;
        foreach($this->options as $option) {
            if(empty($option->price) === false) {
                $total += array_get($option->options, 'price');
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
