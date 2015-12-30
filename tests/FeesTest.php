<?php

class FeesTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testAddFee()
    {
        $this->laracart->addFee(
            'testFee',
            10,
            false, [
                'test' => 3,
            ]
        );

        $fee = $this->laracart->getFee('testFee');

        $this->assertEquals('$10.00', $fee->getAmount());
    }

    public function testMultipleFees()
    {

    }

    public function testRemoveFee()
    {

    }
}
