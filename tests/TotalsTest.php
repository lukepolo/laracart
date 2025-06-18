<?php

/**
 * Class TotalsTest.
 */
class TotalsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test total discounts.
     */
    public function testdiscountTotal()
    {
        $this->addItem(1, 10);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF',
            10
        );

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $this->laracart->discountTotal());
        $this->assertEquals(10, $this->laracart->discountTotal(false));

        $this->assertEquals(0, $this->laracart->total(false));
    }

    /**
     * Test total discounts when using the pricing_in_cents config setting.
     */
    public function testdiscountTotalInCents()
    {
        $this->app['config']->set('laracart.prices_in_cents', true);
        $this->addItem(1, 1000);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF',
            1000
        );

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $this->laracart->discountTotal());
        $this->assertEquals(1000, $this->laracart->discountTotal(false));

        $this->assertEquals(0, $this->laracart->total(false));
    }

    /**
     * Test total taxes.
     */
    public function testTaxTotal()
    {
        $this->addItem();

        $this->assertEquals('$0.07', $this->laracart->taxTotal());
        $this->assertEquals('0.07', $this->laracart->taxTotal(false));
    }

    /**
     * Test total taxes when using the pricing_in_cents config setting.
     */
    public function testTaxTotalInCents()
    {
        $this->app['config']->set('laracart.prices_in_cents', true);
        $this->addItem(1, 100);

        $this->assertEquals('$0.07', $this->laracart->taxTotal());
        $this->assertEquals(7, $this->laracart->taxTotal(false));
    }

    /**
     * Test getting all the fees.
     */
    public function testFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->feeSubTotal());
        $this->assertEquals(25, $this->laracart->feeSubTotal(false));
    }

    /**
     * Test getting a sub total (without tax).
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
     * Test getting the final total (with tax).
     */
    public function testTotal()
    {
        $this->addItem();

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total(false));
    }

    /**
     * Test getting the final total (with tax) when using the pricing_in_cents config setting.
     */
    public function testTotalInCents()
    {
        $this->app['config']->set('laracart.prices_in_cents', true);
        $this->addItem(1, 100);

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals(107, $this->laracart->total(false));
    }

    /**
     * Test the taxable fees total.
     */
    public function testTaxableFees()
    {
        $this->app['config']->set('laracart.fees_taxable', true);
        $this->laracart->addFee('test_2', 1, true, ['tax' => 0.07]);

        $this->assertEquals(1, $this->laracart->feeSubTotal(false));

        $this->assertEquals('0.07', $this->laracart->taxTotal(false));
    }

    /**
     * Test making sure items are taxable and not taxable.
     */
    public function testTaxableItems()
    {
        $this->addItem();
        $item = $this->addItem(1, 2, false);

        // only 1 dollar is taxable!
        $this->assertEquals('3.07', $this->laracart->total(false));

        $item->qty = 5;

        // 3 * 5 = 15 - 5 = 10 , only 10 is taxable

        // only 5 dollar is taxable!
        $this->assertEquals('11.00', $this->laracart->subTotal(false));
        $this->assertEquals('11.07', $this->laracart->total(false));
    }

    /**
     * Test taxable item with taxable fees.
     */
    public function testTotalTaxableItemTaxableFees()
    {
        $tax = .10;
        $priceItem = 10;
        $this->addItem(1, $priceItem, true, ['tax' => $tax]);
        $this->assertEquals(11, $this->laracart->total(false));

        $this->app['config']->set('laracart.fees_taxable', true);
        $fee = 10;
        $this->laracart->addFee('test', $fee, true, ['tax' => $tax]);

        $this->assertEquals($priceItem, $this->laracart->feeSubTotal(false));
        $this->assertEquals($priceItem, $this->laracart->subTotal(false));
        $this->assertEquals($priceItem + $fee, $this->laracart->netTotal(false));
        $taxTotal = ($priceItem * .10) + ($fee * .10);
        $this->assertEquals($taxTotal, $this->laracart->taxTotal(false));
        $this->assertEquals($priceItem + $fee + $taxTotal, $this->laracart->total(false));
    }

    /**
     * Test NOT taxable item with taxable fees.
     */
    public function testTotalNotTaxableItemTaxableFees()
    {
        $this->app['config']->set('laracart.fees_taxable', true);

        $tax = .20;
        $priceItem = 5;
        $priceFee = 2;

        $this->addItem(1, $priceItem, false);
        $this->laracart->addFee('test', $priceFee, true, ['tax' => $tax]);

        $this->assertEquals('2.00', $this->laracart->feeSubTotal(false, true));
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

        $this->assertEquals('2.00', $this->laracart->feeSubTotal(false));
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

        $this->assertEquals('2.00', $this->laracart->feeSubTotal(false));
        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('7.00', $this->laracart->total(false));
    }

    /**
     * Test NOT taxable item with taxable fees.
     */
    public function testTotalDifferentTaxItemAndFees()
    {
        $this->app['config']->set('laracart.fees_taxable', true);
        $taxItem = .20;
        $taxFee = .07;
        $priceItem = 5;
        $priceFee = 2;

        $this->addItem(1, $priceItem, true, ['tax' => $taxItem]);

        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('6.00', $this->laracart->total(false));

        $this->laracart->addFee('test', $priceFee, true, ['tax' => $taxFee]);
        $this->assertEquals('2.00', $this->laracart->feeSubTotal(false));
        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('7.00', $this->laracart->netTotal(false));
        $this->assertEquals('8.14', $this->laracart->total(false));
    }

    public function testActivateAndDeactivate()
    {
        $item = $this->addItem();

        $this->assertEquals('1.07', $this->laracart->total(false));

        $item->disable();

        $this->assertEquals(0, $this->laracart->subTotal(false));

        $item->enable();

        $this->assertEquals('1.07', $this->laracart->total(false));
    }

    public function testTotalWithoutTaxableFees()
    {
        $this->addItem(5);

        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('5.35', $this->laracart->total(false));

        $this->laracart->addFee('test', 1);

        $this->assertEquals('6.00', $this->laracart->netTotal(false));
        $this->assertEquals('6.35', $this->laracart->total(false));
    }

    public function testTaxTotalWithDiscounts()
    {
        $this->laracart->add(1, 'Test Product', 1, 100, ['tax' => 0.21]);

        $coupon = new LukePOLO\LaraCart\Coupons\Percentage('test', 0.05, [
            'name'        => '5% off',
            'description' => '5% off test',
        ]);

        $this->laracart->addCoupon($coupon);

        $this->assertEquals(100, $this->laracart->subTotal(false));
        $this->assertEquals(5, $this->laracart->discountTotal(false));
        $this->assertEquals(19.95, $this->laracart->taxTotal(false));
        $this->assertEquals(114.95, $this->laracart->total(false));
    }

    public function testDoubleDiscounts()
    {
        $item = $this->laracart->add(1, 'Test Product', 1, 100, ['tax' => 0.21]);

        $coupon = new LukePOLO\LaraCart\Coupons\Percentage('test', 0.05, [
            'name'        => '5% off',
            'description' => '5% off test',
        ]);

        $this->laracart->addCoupon($coupon);
        $coupon->setDiscountOnItem($item);

        $this->assertEquals(100, $this->laracart->subTotal(false));
        $this->assertEquals(5, $this->laracart->discountTotal(false));
        $this->assertEquals(95, $this->laracart->netTotal(false));
        $this->assertEquals(19.95, $this->laracart->taxTotal(false));
        $this->assertEquals(114.95, $this->laracart->total(false));
    }

    public function testTaxationOnCoupons()
    {
        // Add to cart
        $this->laracart->add(
            1,
            'test',
            52,
            107.44,
            [
                'tax' => 0.21,
            ]
        );

        $this->assertEquals(5586.88, $this->laracart->subTotal(false));
        $this->assertEquals(0, $this->laracart->discountTotal(false));
        $this->assertEquals(1173.24, $this->laracart->taxTotal(false));
        $this->assertEquals(6760.00, $this->laracart->total(false));

        // Test discount %
        $coupon = new LukePOLO\LaraCart\Coupons\Percentage('7.5%', 0.075);
        $this->laracart->addCoupon($coupon);

        $this->assertEquals(5586.88, $this->laracart->subTotal(false));
        $this->assertEquals(419.02, $this->laracart->discountTotal(false));
        $this->assertEquals(1085.25, $this->laracart->taxTotal(false));
        $this->assertEquals(6253.10, $this->laracart->total(false));

        $this->laracart->removeCoupons();

        // Test discount fixed
        $coupon = new LukePOLO\LaraCart\Coupons\Fixed('100 euro', 100);
        $this->laracart->addCoupon($coupon);

        $this->assertEquals(5586.88, $this->laracart->subTotal(false));
        $this->assertEquals(100, $this->laracart->discountTotal(false));
        $this->assertEquals(1152.24, $this->laracart->taxTotal(false));
        $this->assertEquals(6639.00, $this->laracart->total(false));
    }

    public function testBasicTotalsWithItemTax()
    {
        $this->app['config']->set('laracart.tax', .19);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        );

        $this->assertEquals(100, $this->laracart->subTotal(false));
        $this->assertEquals(0, $this->laracart->discountTotal(false));
        $this->assertEquals(19, $this->laracart->taxTotal(false));
        $this->assertEquals(119, $this->laracart->total(false));
    }

    public function testDiscountsOnMultiQtyItems()
    {
        $this->laracart->emptyCart();
        $this->laracart->destroyCart();

        $item = $this->laracart->add(123, 'T-Shirt', 2, 100, ['tax' => .2], true);

        $coupon = new \LukePOLO\LaraCart\Coupons\Percentage('10%OFF', 0.10);
        $this->laracart->addCoupon($coupon);
        $coupon->setDiscountOnItem($item);

        $this->assertEquals($item->getDiscount(false), 20);
        $this->assertEquals($this->laracart->subTotal(false), 200);
        $this->assertEquals($this->laracart->discountTotal(false), 20);

        $this->assertEquals($this->laracart->taxTotal(false), 36);
        $this->assertEquals($this->laracart->total(false), 216);
    }

    /**
     * Test round of prices. Only the total value should be rounded.
     */
    public function testRoundOnlyTotalValue()
    {
        $item = $this->addItem();
        $item->addSubItem([
            'description' => 'First item',
            'price'       => 8.40336,
            'qty'         => 1,
        ]);

        $item->addSubItem([
            'description' => 'Second item',
            'price'       => 4.20168,
            'qty'         => 1,
        ]);
        $this->assertEquals(13.61, $this->laracart->subTotal(false));
    }

    public function testCartTaxSumary()
    {
        $this->app['config']->set('laracart.fees_taxable', true);
        $item = $this->addItem(1, 10, true, [
            'tax' => .01,
        ]);

        $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 10.00,
            'taxable' => true,
            'tax'     => .02,
        ]);

        $item = $this->addItem(1, 12, true, [
            'tax' => .01,
        ]);

        $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 10.00,
            'taxable' => true,
            'tax'     => .02,
        ]);

        $this->laracart->addFee(
            'cart fee',
            5.00,
            true,
            [
                'tax' => .03,
            ]
        );

        $this->assertEquals([
            '0.01' => .22,
            '0.02' => .40,
            '0.03' => .15,
        ], $this->laracart->taxSummary());
    }

    public function testQtyOnSubItems()
    {
        $item = $this->addItem(1, 0);

        $item->addSubItem([
            'description' => 'Ticket: Erwachsener',
            'price'       => 18.48739,
            'qty'         => 2,
            'tax'         => .19,
        ]);

        $this->assertEquals(40.49, $this->laracart->total(false));
    }

    public function testSubTotalTaxRounding()
    {
        $item = $this->addItem(1, 0);

        $item->addSubItem([
            'description' => 'Ticket: Erwachsener',
            'price'       => 18.48739,
            'qty'         => 1,
            'tax'         => .19,
        ]);
        // 18.48739 + (18.48739 *.19) = 21.9999941

        $item->addSubItem([
            'description' => 'Ticket: Ermäßigt',
            'price'       => 16.80672,
            'qty'         => 1,
            'tax'         => .19,
        ]);

        // 16.80672 + (16.80672 *.19) = 19.9999968

        // 21.9999941 + 19.9999968 = 41.9999909

        $this->assertEquals(42.00, $this->laracart->total(false));
    }
}
