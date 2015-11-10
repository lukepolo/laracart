<?php

class LaraCartTest extends Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('laracart.tax', '.07');
    }

    public function setUp()
    {
        parent::setUp();
        $this->laracart = new \LukePOLO\LaraCart\LaraCart();
    }

    protected function getPackageProviders($app)
    {
        return ['\LukePOLO\LaraCart\LaraCartServiceProvider'];
    }

    public function testGetInstance()
    {
        $this->assertEquals(new \LukePOLO\LaraCart\LaraCart(), $this->laracart->get());
    }

    public function testSetInstance()
    {
        $this->assertNotEquals(new \LukePOLO\LaraCart\LaraCart(), $this->laracart->setInstance('test'));
    }

    public function testUpdate()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(1, $this->laracart->get()->count());
    }

    public function testFormatMoney()
    {
        $this->assertEquals('$25.00', $this->laracart->formatMoney('25.00'));
    }

    public function testGetAndSetAndRemoveAttribute()
    {
        $this->assertEquals(null, $this->laracart->getAttribute('test'));

        $this->laracart->setAttribute('test', 2);

        $this->assertEquals(2, $this->laracart->getAttribute('test'));

        $this->laracart->removeAttribute('test');

        $this->assertEquals(null, $this->laracart->getAttribute('test'));
    }

    public function testGetAttributes()
    {
        $this->assertInternalType('array', $this->laracart->getAttributes());
    }

    public function testGetItem()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));
    }

    public function testGetItems()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->add(
            '2',
            'Testing Item',
            2,
            '2',
            [
                'c_test' => 'option_3',
                'd_test' => 'option_4',
            ]
        );

        $items = $this->laracart->getItems();
        $this->assertInternalType('array', $items);
        $this->assertCount(2, $items);
        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $items);
    }

    public function testAdd()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(1, $this->laracart->count(false));
    }

    public function testAddLine()
    {
        $this->laracart->addLine(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->addLine(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(2, $this->laracart->count(false));
    }

    public function testItem()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->updateItem($item->getHash(), 'b_test', 3);

        $item = $this->laracart->getItem($item->getHash());

        $this->assertEquals('3', $item->b_test);
        $this->assertEquals(1, $item->price);
        $this->assertTrue(isset($item->b_test));
        $this->assertFalse(isset($item->z_test));


        $item->update('qty', 3);
        $item->update('price', 10);

        $this->assertEquals(3, $item->qty);
        $this->assertEquals(10, $item->price);
    }

    public function testSubItem()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $subItemData = [
            'name' => 'testSubItem',
            'price' => 2,
        ];

        $subItem = $item->addSubItem($subItemData);

        $item->findSubItem($subItem->getHash());

        $this->assertEquals(new \LukePOLO\LaraCart\CartSubItem($subItemData), $subItem);
        $this->assertEquals('2', $subItem->price);
        $this->assertEquals('$2.00', $subItem->getPrice());

        $subItem->test = 4;

        $this->assertEquals(4, $subItem->test);

        $this->assertTrue(isset($subItem->test));
        $this->assertFalse(isset($subItem->testz));


        $this->assertEquals("$3.00", $item->getPrice());

        $subItem->items[] = new \LukePOLO\LaraCart\CartItem(
            '123', 'subItem', 1, 3, [
                'testing' => 1,
            ]
        );

        $this->assertEquals('$8.00', $item->subTotal());
    }

    public function testRemoveItem()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(1, $this->laracart->count());
        $this->laracart->removeItem($item->getHash());
        $this->assertEmpty($this->laracart->getItem($item->getHash()));
    }

    public function testCount()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            2,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(2, $this->laracart->count());
        $this->assertEquals(1, $this->laracart->count(false));
    }

    public function testEmptyCart()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            2,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->setAttribute('test', 1);

        $this->laracart->emptyCart();

        $this->assertEquals(1, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    public function testDestroyCart()
    {
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            2,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->setAttribute('test', 1);

        $this->laracart->destroyCart();

        $this->assertEquals(null, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    public function testAddCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);
        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '.1');
        $this->laracart->addCoupon($percentCoupon);
        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('10%OFF'));
        $this->assertEquals('10%', $percentCoupon->displayValue());

        $this->laracart->addFee(
            'test',
            10,
            false,
            [
                'test' => 3,
            ]
        );

        $this->assertEquals('1', $percentCoupon->discount());

        $this->assertCount(2, $this->laracart->getCoupons());
    }

    public function testRemoveCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));
        $this->laracart->removeCoupon('10OFF');
        $this->assertEmpty($this->laracart->findCoupon('10OFF'));
    }

    public function testAddFee()
    {
        $amount = 10;
        $taxable = false;
        $options = [
            'test' => 1,
        ];

        $this->laracart->addFee('test', $amount, $taxable, $options);

        $this->assertEquals(
            new \LukePOLO\LaraCart\CartFee($amount, $taxable, $options),
            $this->laracart->getFee('test')
        );

        $this->assertCount(1, $this->laracart->getFees());

        $this->laracart->removeFee('test');

        $this->assertCount(0, $this->laracart->getFees());
    }

    public function testFeeFunctions()
    {
        $this->laracart->addFee(
            'test',
            10,
            false,
            [
                'test' => 3,
            ]
        );

        $fee = $this->laracart->getFee('test');

        $this->assertEquals(3, $fee->test);
        $this->assertEquals('$10.00', $fee->getAmount());

        $fee->test2 = '1';
        $this->assertTrue(isset($fee->test2));
        $this->assertFalse(isset($fee->test3));
        $this->assertEquals(1, $fee->test2);


        $this->laracart->addFee('test', 1, true);

        $this->assertEquals('$1.07', $this->laracart->feeTotals());
    }

    public function testFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->feeTotals());
        $this->assertEquals(25, $this->laracart->feeTotals(false));

    }

    public function testTotalDiscount()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed(
            '10OFF', 10, [
                'test' => 1,
            ]
        );
        $this->laracart->addCoupon($fixedCoupon);

        $fixedCouponInCart = $this->laracart->findCoupon('10OFF');
        $this->assertEquals($fixedCoupon, $fixedCouponInCart);

        $this->assertEquals('$10.00', $this->laracart->totalDiscount());
        $this->assertEquals(10, $this->laracart->totalDiscount(false));


        $this->assertEquals('Coupon Applied', $fixedCouponInCart->getMessage());
        $this->assertEquals(1, $fixedCouponInCart->test);
        $fixedCoupon->test = 2;
        $this->assertEquals(2, $fixedCouponInCart->test);

        $this->assertTrue(isset($fixedCouponInCart->test));
        $this->assertFalse(isset($fixedCouponInCart->test2));
        $this->assertEquals('$10.00', $fixedCoupon->displayValue());
    }

    public function testTaxTotal()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1.00',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal(false));
    }

    public function testSubTotal()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '24.00',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals('$24.00', $this->laracart->subTotal());
        $this->assertEquals('24.00', $this->laracart->subTotal(false, false));
    }

    public function testTotal()
    {
        // TODO - Test taxable fees
        $item = $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1.00',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total(false));
    }
}
