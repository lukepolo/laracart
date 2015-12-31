<?php

class MagicFunctionsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testGet()
    {
        $item = $this->addItem();

        $this->assertEquals('option_1', $item->b_test);
    }

    public function testSet()
    {
        $item = $this->addItem();

        $item->test_option = 123;

        $this->assertEquals(123, $item->test_option);
    }

    public function testIsset()
    {
        $item = $this->addItem();

        $this->assertEquals(true, isset($item->b_test));
        $this->assertEquals(false, isset($item->testtestestestsetset));
    }
}
