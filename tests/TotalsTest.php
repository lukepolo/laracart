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
    public function testTotalDiscount()
    {
        $this->addItem(1, 10);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF',
            10
        );

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $this->laracart->totalDiscount());
        $this->assertEquals(10, $this->laracart->totalDiscount(false));

        $this->assertEquals(0, $this->laracart->total(false));
    }

    /**
     * Test total discounts when using the pricing_in_cents config setting.
     */
    public function testTotalDiscountInCents()
    {
        $this->app['config']->set('laracart.prices_in_cents', true);
        $this->addItem(1, 1000);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF',
            1000
        );

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $this->laracart->totalDiscount());
        $this->assertEquals(1000, $this->laracart->totalDiscount(false));

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

        $this->assertEquals('$25.00', $this->laracart->feeTotals());
        $this->assertEquals(25, $this->laracart->feeTotals(false));
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
        $this->laracart->addFee('test_2', 1, true, ['tax' => 0.07]);

        $this->assertEquals('$1.00', $this->laracart->feeTotals());
        $this->assertEquals(1, $this->laracart->feeTotals(false));

        $this->assertEquals('$0.07', $this->laracart->taxTotal());
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

        $this->assertEquals('5.00', $this->laracart->subTotal(false));
        $this->assertEquals('6.00', $this->laracart->total(false));

        $this->laracart->addFee('test', $priceFee, true, ['tax' => $taxFee]);
        $this->assertEquals('2.14', $this->laracart->feeTotals(false, true));

        $this->assertEquals('7.00', $this->laracart->total(false, false, false));
        $this->assertEquals('8.14', $this->laracart->total(false, false));
    }

    public function testActivateAndDeactivate()
    {
        $item = $this->addItem();

        $this->assertEquals('1.07', $this->laracart->total(false));

        $item->disable();

        $this->assertEquals(0, $this->laracart->total(false));

        $item->enable();

        $this->assertEquals('1.07', $this->laracart->total(false));
    }

    public function testTotalWithoutFees()
    {
        $this->addItem(5);

        $this->assertEquals('5.35', $this->laracart->total(false));

        $this->laracart->addFee('test', 1, true);

        $this->assertEquals('6.42', $this->laracart->total(false));

        $this->assertEquals('6.00', $this->laracart->total(false, true, false));

        $this->assertEquals('5.35', $this->laracart->total(false, true, true, false));

        $this->assertEquals('5.00', $this->laracart->total(false, true, false, false));
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
        $this->assertEquals(5, $this->laracart->totalDiscount(false));
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

        $this->assertEquals(95, $this->laracart->subTotal(false));
        $this->assertEquals(5, $this->laracart->totalDiscount(false));
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
        $this->assertEquals(0, $this->laracart->totalDiscount(false));
        $this->assertEquals(1173.24, $this->laracart->taxTotal(false));
        $this->assertEquals(6760.12, $this->laracart->total(false));

        // Test discount %
        $coupon = new LukePOLO\LaraCart\Coupons\Percentage('7,5%', 0.075);
        $this->laracart->addCoupon($coupon);

        $this->assertEquals(5586.88, $this->laracart->subTotal(false));
        $this->assertEquals(419.02, $this->laracart->totalDiscount(false));
        $this->assertEquals(1085.25, $this->laracart->taxTotal(false));
        $this->assertEquals(6253.11, $this->laracart->total(false));

        $this->laracart->removeCoupons();

        // Test discount fixed
        $coupon = new LukePOLO\LaraCart\Coupons\Fixed('100 euro', 100);
        $this->laracart->addCoupon($coupon);

        $this->assertEquals(5586.88, $this->laracart->subTotal(false));
        $this->assertEquals(100, $this->laracart->totalDiscount(false));
        $this->assertEquals(1152.24, $this->laracart->taxTotal(false));
        $this->assertEquals(6639.12, $this->laracart->total(false));
    }

    public function testBasicTotalsWithItemTax()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);

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
        $this->assertEquals(0, $this->laracart->totalDiscount(false));
        $this->assertEquals(19, $this->laracart->taxTotal(false));
        $this->assertEquals(119, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithFixed100PercentOff()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        $this->laracart->add(1, 'Produkt mit 19%', 1, 100, [\LukePOLO\LaraCart\CartItem::ITEM_TAX => (19 / 100)])
            ->addCoupon(new \LukePOLO\LaraCart\Coupons\Fixed('50EUR', 119, [
                'description' => '50EUR',
            ]));

        $this->assertEquals(0, $this->laracart->subTotal(false));
        $this->assertEquals(119, $this->laracart->totalDiscount(false));
        $this->assertEquals(0, $this->laracart->taxTotal(false));
        $this->assertEquals(0, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithPercentageAt100PercentCoupon()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Percentage('50EUR', 1, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(0, $this->laracart->subTotal(false));
        $this->assertEquals(119, $this->laracart->totalDiscount(false));
        $this->assertEquals(0, $this->laracart->taxTotal(false));
        $this->assertEquals(0, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithFixedCoupons()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Fixed('50EUR', 50, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(57.98, $this->laracart->netTotal(false));
        $this->assertEquals(50, $this->laracart->totalDiscount(false));
        $this->assertEquals(11.02, $this->laracart->taxTotal(false));
        $this->assertEquals(69.00, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithPercentageCoupon()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Percentage('50EUR', .5, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(50.00, $this->laracart->netTotal(false));
        $this->assertEquals(119 * .5, $this->laracart->totalDiscount(false));
        $this->assertEquals(9.50, $this->laracart->taxTotal(false));
        $this->assertEquals(59.50, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithFixedCouponsTest2()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Fixed('100EUR', 100, [
            'description' => '100EUR',
        ]));

        $this->assertEquals(15.97, $this->laracart->netTotal(false));
        $this->assertEquals(100, $this->laracart->totalDiscount(false));
        $this->assertEquals(3.03, $this->laracart->taxTotal(false));
        $this->assertEquals(19, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithFixedCouponsTest3()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Percentage('85', .85, [
            'description' => '85',
        ]));

        $this->assertEquals(15.00, $this->laracart->netTotal(false));
        $this->assertEquals(101.15, $this->laracart->totalDiscount(false));
        $this->assertEquals(2.85, $this->laracart->taxTotal(false));
        $this->assertEquals(17.85, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithPercentageCouponsWith84PercentDollar()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Percentage('50EUR', .84, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(16.00, $this->laracart->netTotal(false));
        $this->assertEquals(99.96, $this->laracart->totalDiscount(false));
        $this->assertEquals(3.04, $this->laracart->taxTotal(false));
        $this->assertEquals(19.04, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountWithPercentageCouponsWith1Dollar()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Percentage('50EUR', .99, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(1, $this->laracart->netTotal(false));
        $this->assertEquals(117.81, $this->laracart->totalDiscount(false));
        $this->assertEquals(.19, $this->laracart->taxTotal(false));
        $this->assertEquals(1.19, $this->laracart->total(false));
    }

    public function testPreTaxationAndDiscountSubTotalOnItem()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.tax_by_item', true);
        $this->app['config']->set('laracart.discountTaxable', true);
        $this->app['config']->set('laracart.discountsAlreadyTaxed', true);
        $this->app['config']->set('laracart.tax_item_before_discount', true);

        /* @var \LukePOLO\LaraCart\CartItem $item */
        $item = $this->laracart->add(
            1,
            'Product with 19% Tax',
            1,
            100,
            [
                \LukePOLO\LaraCart\CartItem::ITEM_TAX => .19,
            ]
        )->addCoupon(new \LukePOLO\LaraCart\Coupons\Fixed('50EUR', 118, [
            'description' => '50EUR',
        ]));

        $this->assertEquals(1, $item->subTotal(false, true, false, true));
    }

    public function testDiscountsOnMultiQtyItems()
    {
        $this->laracart->emptyCart();
        $this->laracart->destroyCart();

        $item = $this->laracart->add(123, 'T-Shirt', 2, 100, ['tax' => .2], true);

        $coupon = new \LukePOLO\LaraCart\Coupons\Percentage('10%OFF', 0.10);
        $this->laracart->addCoupon($coupon);
        $coupon->setDiscountOnItem($item);

        $resume = [
            'subTotal'             => $this->laracart->subTotal(true, false),
            'totalDiscount'        => $this->laracart->totalDiscount(true),
            'subTotalWithDiscount' => $this->laracart->subTotal(true, true),
            'taxTotal'             => $this->laracart->taxTotal(true),
            'netTotal'             => $this->laracart->netTotal(true),
            'feeTotals'            => $this->laracart->feeTotals(true, true),
            'total'                => $this->laracart->total(true, true),
        ];

        $this->assertEquals($item->getDiscount(false), 20);
        $this->assertEquals($this->laracart->subTotal(false, false), 200);

        $this->assertEquals($this->laracart->netTotal(false), 184);
        $this->assertEquals($this->laracart->taxTotal(false, false), 36);
        $this->assertEquals($this->laracart->total(false, false), 216);
    }
}
