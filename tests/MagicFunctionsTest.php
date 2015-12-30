<?php

class MagicFunctionsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function getTest()
    {
        $item = $this->addItem();

        $this->assertEquals('option_1', $item->b_test);
    }

    public function setTest()
    {
        $item = $this->addItem();

        $this->test_option = 123;

        $this->assertEquals(123, $item->test_option);
    }

    public function issetTest()
    {
        $item = $this->addItem();

        $this->assertEquals(true, isset($item->option_1));
        $this->assertEquals(false, isset($item->testtestestestsetset));
    }
}
