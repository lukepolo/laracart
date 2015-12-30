<?php

class CouponsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testAddPercentageCoupon()
    {
        $this->addItem(3, 1);

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '.1');

        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('10%OFF'));

        $this->assertEquals('10%', $percentCoupon->displayValue());
        $this->assertEquals('.32', $percentCoupon->discount());

        $this->assertCount(1, $this->laracart->getCoupons());
    }

    public function testAddFixedCoupon()
    {
        $this->addItem();

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('$10.00', $fixedCoupon->displayValue());
        $this->assertEquals('10', $fixedCoupon->discount());

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));
    }

    public function testSingleCoupons()
    {
        $fixedCouponOne = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $fixedCouponTwo = new LukePOLO\LaraCart\Coupons\Fixed('5OFF', 5);

        $this->laracart->addCoupon($fixedCouponOne);
        $this->laracart->addCoupon($fixedCouponTwo);

        $this->assertCount(1, $this->laracart->getCoupons());

        $this->assertEquals($fixedCouponTwo, $this->laracart->findCoupon('5OFF'));
    }

    public function testMultipleCoupons()
    {
        $cart = $this->laracart->get()->cart;
        $cart->multipleCoupons = true;

        $fixedCouponOne = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $fixedCouponTwo = new LukePOLO\LaraCart\Coupons\Fixed('5OFF', 5);

        $this->laracart->addCoupon($fixedCouponOne);
        $this->laracart->addCoupon($fixedCouponTwo);

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

    public function testGetMessage()
    {

    }

    public function testCheckMinAmount()
    {

    }

    public function testMaxDiscount()
    {

    }

    public function testCheckValidTimes()
    {

    }

    public function testSetDiscountOnItem()
    {

    }
}
