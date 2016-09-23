<?php

/**
 * Class TotalsTest
 */
class TotalsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test total discounts
     */
    public function testTotalDiscount()
    {
        $this->addItem(1, 10);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF', 10
        );

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $this->laracart->totalDiscount());
        $this->assertEquals(10, $this->laracart->totalDiscount()->amount());

        $this->assertEquals(0, $this->laracart->total()->amount());
    }

    /**
     * test total taxes
     */
    public function testTaxTotal()
    {
        $this->addItem();

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal()->amount());
    }

    /**
     * Test getting all the fees
     */
    public function testFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->feeTotals());
        $this->assertEquals(25, $this->laracart->feeTotals()->amount());

    }

    /**
     * Test getting a sub total (without tax)
     */
    public function testSubTotal()
    {
        $item = $this->addItem(1, 24);

        $this->assertEquals('$24.00', $this->laracart->subTotal());
        $this->assertEquals('24.00', $this->laracart->subTotal()->amount());

        $item->qty = 5;

        $this->assertEquals('120.00', $this->laracart->subTotal()->amount());
    }

    /**
     * Test getting the final total (with tax)
     */
    public function testTotal()
    {
        $this->addItem();

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total()->amount());
    }

    /**
     * Test the taxable fees total
     */
    public function testTaxableFees()
    {
        $this->laracart->addFee('test_2', 1, ['tax' => 0.07]);

        $this->assertEquals('$1.00', $this->laracart->feeTotals());
        $this->assertEquals(1, $this->laracart->feeTotals()->amount());

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal()->amount());
    }

    /**
     * Test making sure items are taxable and not taxable
     */
    public function testTaxableItems()
    {
        $this->addItem();
        $item = $this->addNonTaxableItem(1, 2);
        // only 1 dollar is taxable!
        $this->assertEquals('3.07', $this->laracart->total()->amount());

        $item->qty = 5;

        // 3 * 5 = 15 - 5 = 10 , only 10 is taxable

        // only 5 dollar is taxable!
        $this->assertEquals('11.00', $this->laracart->subTotal()->amount());
        $this->assertEquals('11.07', $this->laracart->total()->amount());
    }

    /**
     * Test taxable item with taxable fees.
     */
    public function testTotalTaxableItemTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, ['tax' => $tax]);
        $this->laracart->addFee('test', $priceFee, ['tax' => $tax]);

        $this->assertEquals('2.40', $this->laracart->feeTotals(true)->amount());
        $this->assertEquals('5.00', $this->laracart->subTotal(true)->amount());
        $this->assertEquals('8.40', $this->laracart->total()->amount());
    }

    /**
     * Test NOT taxable item with taxable fees.
     */
    public function testTotalNotTaxableItemTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addNonTaxableItem(1, $priceItem);
        $this->laracart->addFee('test', $priceFee, ['tax' => $tax]);

        $this->assertEquals('2.40', $this->laracart->feeTotals(true)->amount());
        $this->assertEquals('5.00', $this->laracart->subTotal(true)->amount());
        $this->assertEquals('7.40', $this->laracart->total()->amount());
    }

    /**
     * Test taxable item with NOT taxable fees.
     */
    public function testTotalTaxableItemNotTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, ['tax' => $tax]);
        $this->laracart->addNonTaxableFee('test', $priceFee);

        $this->assertEquals('2.00', $this->laracart->feeTotals(false)->amount());
        $this->assertEquals('5.00', $this->laracart->subTotal(false)->amount());
        $this->assertEquals('8.00', $this->laracart->total()->amount());
    }

    /**
     * Test NOT taxable item with NOT taxable fees.
     */
    public function testTotalNotTaxableItemNotTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addNonTaxableItem(1, $priceItem);
        $this->laracart->addNonTaxableFee('test', $priceFee);

        $this->assertEquals('2.00', $this->laracart->feeTotals()->amount());
        $this->assertEquals('5.00', $this->laracart->subTotal()->amount());
        $this->assertEquals('7.00', $this->laracart->total()->amount());
    }

    /**
     * Test NOT taxable item with taxable fees.
     */
    public function testTotalDifferentTaxItemAndFees()
    {
        $taxItem = .20;
        $taxFee = .07;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, ['tax' => $taxItem]);
        $this->laracart->addFee('test', $priceFee, ['tax' => $taxFee]);

        $this->assertEquals('2.14', $this->laracart->feeTotals(true)->amount());
        $this->assertEquals('5.00', $this->laracart->subTotal(true)->amount());
        $this->assertEquals('8.14', $this->laracart->total()->amount());
    }

    public function testIntegers()
    {
        $item = $this->addItem(1, 451.51);

        $this->assertEquals(45151, $this->laracart->subTotal()->asInt());
    }
}
