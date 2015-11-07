<?php

class LaraCartTest extends Orchestra\Testbench\TestCase
{
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

    public function testAddItem()
    {
    }

    public function testUpdateItem()
    {
    }

    public function testUpdateItemHash()
    {
    }

    public function testUpdateItemHashes()
    {
    }

    public function testRemoveItem()
    {

    }

    public function testCount()
    {

    }

    public function testEmptyCart()
    {
    }

    public function testDestroyCart()
    {
    }

    public function testGetCoupons()
    {
    }

    public function testFindCoupon()
    {
    }

    public function testApplyCoupon( )
    {
    }

    public function testRemoveCoupon()
    {
    }

    public function testGetFee()
    {
    }


    public function testGetFees()
    {
    }

    public function testAddFee()
    {
    }

    public function testRemoveFee()
    {
    }


    public function testGetFeeTotals()
    {

    }

    public function testGetTotalDiscount()
    {

    }

    public function testTaxTotal()
    {
    }

    public function testSubTotal()
    {

    }

    public function testTotal()
    {

    }
}