<?php

namespace LukePOLO\LaraCart\Contracts;

/**
 * Interface LaraCartContract
 *
 * @package LukePOLO\LaraCart
 */
interface LaraCartContract
{
    /**
     * Formats the number into a money format based on the locale and international formats
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     *
     * @return string
     */
    public function formatMoney(float $number, $locale = null, $internationalFormat = null);

    /**
     * Generates a hash for an object
     *
     * @param $object
     * @return string
     */
    public function generateHash($object);

    /**
     * Generates a random hash
     *
     * @return string
     */
    public function generateRandomHash();
}
