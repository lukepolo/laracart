<?php

/**
 * Class FeesTest.
 */
class FeesTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Add a fee.
     *
     * @param $name
     * @param int $fee
     */
    private function addFee($name, $fee = 10)
    {
        $this->laracart->addFee(
            $name,
            $fee
        );
    }

    /**
     * Add a fee with tax.
     *
     * @param $name
     * @param int $fee
     */
    private function addFeeTax($name, $fee = 100, $tax = 0.21)
    {
        $this->laracart->addFee(
            $name,
            $fee,
            true,
            [
                'tax' => $tax,
            ]
        );
    }

    /**
     * Testing add a fee to the cart.
     */
    public function testAddFee()
    {
        $this->addFee('testFeeOne');

        $fee = $this->laracart->getFee('testFeeOne');

        $this->assertEquals('$10.00', $fee->getAmount());
        $this->assertEquals(10, $fee->getAmount(false));
    }

    /**
     * Testing add a fee to the cart with tax.
     */
    public function testAddFeeTax()
    {
        $this->addFeeTax('testFeeOne');

        $fee = $this->laracart->getFee('testFeeOne');

        $this->assertEquals('$100.00', $fee->getAmount(true, false));
        $this->assertEquals('$121.00', $fee->getAmount(true, true));

        $this->assertEquals(100, $fee->getAmount(false, false));
        $this->assertEquals(121, $fee->getAmount(false, true));
    }

    /**
     * Test if we can add multiple fees to the cart.
     */
    public function testMultipleFees()
    {
        $this->addFee('testFeeOne');
        $this->addFee('testFeeTwo', 20);

        $this->assertEquals('$10.00', $this->laracart->getFee('testFeeOne')->getAmount());
        $this->assertEquals('$20.00', $this->laracart->getFee('testFeeTwo')->getAmount());

        $this->assertEquals(10, $this->laracart->getFee('testFeeOne')->getAmount(false));
        $this->assertEquals(20, $this->laracart->getFee('testFeeTwo')->getAmount(false));
    }

    /**
     * Test if we can add multiple fees to the cart.
     */
    public function testMultipleFeesTax()
    {
        $this->addFeeTax('testFeeOne');
        $this->addFeeTax('testFeeTwo', 200);

        $this->assertEquals('$100.00', $this->laracart->getFee('testFeeOne')->getAmount(true, false));
        $this->assertEquals('$121.00', $this->laracart->getFee('testFeeOne')->getAmount(true, true));

        $this->assertEquals('$242.00', $this->laracart->getFee('testFeeTwo')->getAmount(true, true));
        $this->assertEquals('$200.00', $this->laracart->getFee('testFeeTwo')->getAmount(true, false));

        $this->assertEquals(121, $this->laracart->getFee('testFeeOne')->getAmount(false, true));
        $this->assertEquals(100, $this->laracart->getFee('testFeeOne')->getAmount(false, false));

        $this->assertEquals(242, $this->laracart->getFee('testFeeTwo')->getAmount(false, true));
        $this->assertEquals(200, $this->laracart->getFee('testFeeTwo')->getAmount(false, false));
    }

    /**
     * Test if we can remove a fee from the cart.
     */
    public function testRemoveFee()
    {
        $this->addFee('testFeeOne');
        $this->assertEquals('$10.00', $this->laracart->getFee('testFeeOne')->getAmount());
        $this->assertEquals(10, $this->laracart->getFee('testFeeOne')->getAmount(false));

        $this->laracart->removeFee('testFeeOne');

        $this->assertEquals('$0.00', $this->laracart->getFee('testFeeOne')->getAmount());
        $this->assertEquals(0, $this->laracart->getFee('testFeeOne')->getAmount(false));
    }

    /**
     * Test if we can remove all fees from the cart.
     */
    public function testRemoveFees()
    {
        $this->addFee('testFeeOne');
        $this->assertEquals('$10.00', $this->laracart->getFee('testFeeOne')->getAmount());
        $this->assertEquals(10, $this->laracart->getFee('testFeeOne')->getAmount(false));

        $this->laracart->removeFees();

        $this->assertTrue(empty($this->laracart->getFees()));
    }
}
