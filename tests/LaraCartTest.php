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
        $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals(1,  $this->laracart->get()->count());
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
        $item = $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));
    }

    public function testGetItems()
    {
        $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->add('2', 'Testing Item', 2, '2', [
            'c_test' => 'option_3',
            'd_test' => 'option_4'
        ]);

        $items = $this->laracart->getItems();
        $this->assertInternalType('array', $items);
        $this->assertCount(2, $items);
        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $items);
    }

    public function testAdd()
    {
        $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals(1, $this->laracart->count(false));
    }

    public function testAddLine()
    {
        $this->laracart->addLine('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->addLine('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals(2, $this->laracart->count(false));
    }

    public function testUpdateItem()
    {
        $item = $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->updateItem($item->getHash(), 'b_test', 3);

        $item = $this->laracart->getItem($item->getHash());

        $this->assertEquals('3', $item->b_test);
    }

    public function testRemoveItem()
    {
        $item = $this->laracart->add('1', 'Testing Item', 1, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals(1, $this->laracart->count());
        $this->laracart->removeItem($item->getHash());
        $this->assertEmpty($this->laracart->getItem($item->getHash()));
    }

    public function testCount()
    {
        $item = $this->laracart->add('1', 'Testing Item', 2, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals(2, $this->laracart->count());
        $this->assertEquals(1 , $this->laracart->count(false));
    }

    public function testEmptyCart()
    {
        $item = $this->laracart->add('1', 'Testing Item', 2, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->setAttribute('test', 1);

        $this->laracart->emptyCart();

        $this->assertEquals(1, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    public function testDestroyCart()
    {
        $item = $this->laracart->add('1', 'Testing Item', 2, '1', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->laracart->setAttribute('test', 1);

        $this->laracart->destroyCart();

        $this->assertEquals(null, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    public function testApplyCoupon( )
    {
        $fixedCoupon = New LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->applyCoupon($fixedCoupon);
        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $percentCoupon = New LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '.1');
        $this->laracart->applyCoupon($percentCoupon);
        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('10%OFF'));

        $this->assertCount(2, $this->laracart->getCoupons());
    }

    public function testRemoveCoupon()
    {
        $fixedCoupon = New LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->applyCoupon($fixedCoupon);

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));
        $this->laracart->removeCoupon('10OFF');
        $this->assertEmpty($this->laracart->findCoupon('10OFF'));
    }

    public function testAddFee()
    {
        $this->laracart->addFee('test', 10);

        $this->assertCount(1, $this->laracart->getFees());

        $this->laracart->removeFee('test');

        $this->assertCount(0, $this->laracart->getFees());
    }

    public function testGetFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->getFeeTotals());
        $this->assertEquals(25, $this->laracart->getFeeTotals(false));

    }

    public function testGetTotalDiscount()
    {
        $fixedCoupon = New LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->applyCoupon($fixedCoupon);
        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $this->assertEquals('$10.00', $this->laracart->getTotalDiscount());
        $this->assertEquals(10, $this->laracart->getTotalDiscount(false));
    }

    public function testTaxTotal()
    {
        $item = $this->laracart->add('1', 'Testing Item', 1, '1.00', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal(false));
    }

    public function testSubTotal()
    {
        $item = $this->laracart->add('1', 'Testing Item', 1, '24.00', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals('$24.00', $this->laracart->subTotal());
        $this->assertEquals('24.00', $this->laracart->subTotal(false, false));
    }

    public function testTotal()
    {
        $item = $this->laracart->add('1', 'Testing Item', 1, '1.00', [
            'b_test' => 'option_1',
            'a_test' => 'option_2'
        ]);

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total(false));
    }
}