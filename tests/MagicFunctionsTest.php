<?php

use LukePOLO\LaraCart\Exceptions\InvalidTaxableValue;

/**
 * Class MagicFunctionsTest.
 */
class MagicFunctionsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test the magic method get.
     */
    public function testGet()
    {
        $item = $this->addItem();

        $this->assertEquals('option_1', $item->b_test);
    }

    /**
     * Test the magic method set.
     */
    public function testSet()
    {
        $item = $this->addItem();

        $item->test_option = 123;

        $this->assertEquals(123, $item->test_option);

        try {
            $item->tax = 'not_a_number';
            $this->setExpectedException(InvalidTaxableValue::class);
        } catch (InvalidTaxableValue $e) {
            $this->assertEquals('The tax must be a number', $e->getMessage());
        }

        try {
            $item->taxable = 123123;
            $this->setExpectedException(InvalidTaxableValue::class);
        } catch (InvalidTaxableValue $e) {
            $this->assertEquals('The taxable option must be a boolean', $e->getMessage());
        }

        $item->taxable = 1;
        $item->taxable = 0;
    }

    /**
     * Test the magic method isset.
     */
    public function testIsset()
    {
        $item = $this->addItem();

        $this->assertEquals(true, isset($item->b_test));
        $this->assertEquals(false, isset($item->testtestestestsetset));
    }
}
