<?php

use Carbon\Carbon;

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
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('Coupon Applied', $this->laracart->findCoupon('10OFF')->getMessage());
    }

    public function testCheckMinAmount()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals(true, $coupon->checkMinAmount(0));
        $this->assertEquals(false, $coupon->checkMinAmount(100, false));

        try {
            $coupon->checkMinAmount(100);
        } catch(\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('You must have at least a total of $100.00', $e->getMessage());
        }
    }

    public function testMaxDiscount()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals(100, $coupon->maxDiscount(0, 100));
        $this->assertEquals(100, $coupon->maxDiscount(5000, 100));
        $this->assertEquals(1, $coupon->maxDiscount(1, 100, false));

        try {
            $coupon->maxDiscount(10, 100);
        } catch(\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('This has a max discount of $10.00', $e->getMessage());
        }
    }

    public function testCheckValidTimes()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals(true, $coupon->checkValidTimes(Carbon::yesterday(), Carbon::tomorrow()));
        $this->assertEquals(false, $coupon->checkValidTimes(Carbon::tomorrow(), Carbon::tomorrow(), false));

        try {
            $this->assertEquals(false, $coupon->checkValidTimes(Carbon::tomorrow(), Carbon::tomorrow()));
        } catch(\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('This coupon has expired', $e->getMessage());
        }
    }

    public function testSetDiscountOnItem()
    {
        $item = $this->addItem();
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $coupon->setDiscountOnItem($item, '10OFF');

        $this->assertEquals('10OFF', $item->code);
    }
}
