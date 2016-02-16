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
        $this->assertEquals(10, $this->laracart->totalDiscount(false));

        $this->assertEquals(0, $this->laracart->total(false));
    }

    /**
     * test total taxes
     */
    public function testTaxTotal()
    {
        $this->addItem();

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal(false));
    }

    /**
     * Test getting all the fees
     */
    public function testFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->feeTotals());
        $this->assertEquals(25, $this->laracart->feeTotals(false));

    }

    /**
     * Test getting a sub total (without tax)
     */
    public function testSubTotal()
    {
        $item = $this->addItem(1, 24);

        $this->assertEquals('$24.00', $this->laracart->subTotal());
        $this->assertEquals('24.00', $this->laracart->subTotal(false));

        $item->qty = 5;

        $this->assertEquals('120.00', $this->laracart->subTotal(false));
    }

    /**
     * Test getting the final total (with tax)
     */
    public function testTotal()
    {
        $this->addItem();

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total(false));
    }

    /**
     * Test the taxable fees total
     */
    public function testTaxableFees()
    {
        $this->laracart->addFee('test_2', 1, true, ['tax' => 0.07]);

        $this->assertEquals('$1.00', $this->laracart->feeTotals());
        $this->assertEquals(1, $this->laracart->feeTotals(false));

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal(false));
    }

    /**
     * Test making sure items are taxable and not taxable
     */
    public function testTaxableItems()
    {
        $this->addItem();
        $item = $this->addItem(1, 2, false);

        $this->assertEquals('$3.07', $this->laracart->total());
        $this->assertEquals('3.07', $this->laracart->total(false));

        $item->qty = 5;

        $this->assertEquals('11.00', $this->laracart->subTotal(false));
        $this->assertEquals('11.07', $this->laracart->total(false));
    }

    /**
     * Test taxable item with taxable fees.
     */
    public function testTotalTaxableItemTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, true, ['tax' => $tax]);
        $this->laracart->addFee('test', $priceFee, true, ['tax' => $tax]);

        $this->assertEquals('2.40', $this->laracart->feeTotals(false, true));
        $this->assertEquals('5.00', $this->laracart->subTotal(false, true));
        $this->assertEquals('8.40', $this->laracart->total(false));
    }

    /**
     * Test NOT taxable item with taxable fees.
     */
    public function testTotalNotTaxableItemTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, false);
        $this->laracart->addFee('test', $priceFee, true, ['tax' => $tax]);

        $this->assertEquals('2.40', $this->laracart->feeTotals(false, true));
        $this->assertEquals('5.00', $this->laracart->subTotal(false, true));
        $this->assertEquals('7.40', $this->laracart->total(false));
    }

    /**
     * Test taxable item with NOT taxable fees.
     */
    public function testTotalTaxableItemNotTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, true, ['tax' => $tax]);
        $this->laracart->addFee('test', $priceFee, false);

        $this->assertEquals('2.00', $this->laracart->feeTotals(false));
        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('8.00', $this->laracart->total(false));
    }

    /**
     * Test NOT taxable item with NOT taxable fees.
     */
    public function testTotalNotTaxableItemNotTaxableFees()
    {
        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $item = $this->addItem(1, $priceItem, false);
        $this->laracart->addFee('test', $priceFee, false);

        $this->assertEquals('2.00', $this->laracart->feeTotals(false));
        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('7.00', $this->laracart->total(false));
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

        $item = $this->addItem(1, $priceItem, true, ['tax' => $taxItem]);
        $this->laracart->addFee('test', $priceFee, true, ['tax' => $taxFee]);

        $this->assertEquals('2.14', $this->laracart->feeTotals(false, true));
        $this->assertEquals('5.00', $this->laracart->subTotal(false, true));
        $this->assertEquals('8.14', $this->laracart->total(false));
    }
}
