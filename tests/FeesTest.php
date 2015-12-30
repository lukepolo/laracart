<?php

class FeesTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    private function addFee($name, $fee = 10)
    {
        $this->laracart->addFee(
            $name,
            $fee
        );
    }

    public function testAddFee()
    {
        $this->addFee('testFeeOne');

        $fee = $this->laracart->getFee('testFeeOne');

        $this->assertEquals('$10.00', $fee->getAmount());
    }

    public function testMultipleFees()
    {
        $this->addFee('testFeeOne');
        $this->addFee('testFeeTwo', 20);

        $this->assertEquals('$10.00', $this->laracart->getFee('testFeeOne')->getAmount());
        $this->assertEquals('$20.00', $this->laracart->getFee('testFeeTwo')->getAmount());
    }

    public function testRemoveFee()
    {
        $this->addFee('testFeeOne');
        $this->assertEquals('$10.00', $this->laracart->getFee('testFeeOne')->getAmount());

        $this->laracart->removeFee('testFeeOne');

        $this->assertEquals('$0.00', $this->laracart->getFee('testFeeOne')->getAmount());
    }
}
