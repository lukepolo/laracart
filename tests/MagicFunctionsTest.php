<?php

/**
 * Class MagicFunctionsTest
 */
class MagicFunctionsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test the magic method get
     */
    public function testGet()
    {
        $item = $this->addItem();

        $this->assertEquals('option_1', $item->b_test);
    }

    /**
     * Test the magic method set
     */
    public function testSet()
    {
        $item = $this->addItem();

        $item->test_option = 123;

        $this->assertEquals(123, $item->test_option);
    }

    /**
     * Test the magic method isset
     */
    public function testIsset()
    {
        $item = $this->addItem();

        $this->assertEquals(true, isset($item->b_test));
        $this->assertEquals(false, isset($item->testtestestestsetset));
    }
}
