<?php

class LaraCartTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testGetInstance()
    {
        $this->assertEquals(new \LukePOLO\LaraCart\LaraCart(), $this->laracart->get());
    }

    public function testSetInstance()
    {
        $this->assertNotEquals(new \LukePOLO\LaraCart\LaraCart(), $this->laracart->setInstance('test'));
    }

    public function testFormatMoney()
    {
        $this->assertEquals('$25.00', $this->laracart->formatMoney('25.00'));
        $this->assertEquals('USD 25.00', $this->laracart->formatMoney('25.00', null, true));
        $this->assertEquals('25.00', $this->laracart->formatMoney('25.00', null, null, false));
    }

    public function testCount()
    {
        $this->addItem(2);

        $this->assertEquals(2, $this->laracart->count());
        $this->assertEquals(1, $this->laracart->count(false));
    }

    public function testEmptyCart()
    {
        $this->addItem();

        $this->laracart->setAttribute('test', 1);

        $this->laracart->emptyCart();

        $this->assertEquals(1, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    public function testDestroyCart()
    {
        $this->addItem();

        $this->laracart->setAttribute('test', 1);

        $this->laracart->destroyCart();

        $this->assertEquals(null, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }
}
