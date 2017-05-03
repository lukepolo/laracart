<?php

use Carbon\Carbon;

/**
 * Class CouponsTest.
 */
class CouponsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test the percentage coupons.
     */
    public function testAddPercentageCoupon()
    {
        $this->addItem(3, 1);

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '.1');

        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('10%OFF'));

        $this->assertEquals('10%', $percentCoupon->displayValue());
        $this->assertEquals('.30', $percentCoupon->discount());

        $this->assertCount(1, $this->laracart->getCoupons());

        $this->assertEquals(.19, $this->laracart->taxTotal(false));
    }

    /**
     * Test the fixed coupons.
     */
    public function testAddFixedCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('0', $fixedCoupon->discount());

        $this->addItem(1, 20);

        $this->assertEquals('$10.00', $fixedCoupon->displayValue());
        $this->assertEquals('10', $fixedCoupon->discount());

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $this->assertEquals('.7', $this->laracart->taxTotal(false));
    }

    /**
     * Test if single coupons works, we souldn't be able to add two.
     */
    public function testSingleCoupons()
    {
        $fixedCouponOne = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $fixedCouponTwo = new LukePOLO\LaraCart\Coupons\Fixed('5OFF', 5);

        $this->laracart->addCoupon($fixedCouponOne);
        $this->laracart->addCoupon($fixedCouponTwo);

        $this->assertCount(1, $this->laracart->getCoupons());

        $this->assertEquals($fixedCouponTwo, $this->laracart->findCoupon('5OFF'));
    }

    /**
     * Test if we can add multiple if the config is set properly.
     */
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

    /**
     * Test removing coupons.
     */
    public function testRemoveCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $this->laracart->removeCoupon('10OFF');

        $this->assertEmpty($this->laracart->findCoupon('10OFF'));
    }

    /**
     * Test getting the message from the coupon to see if its valid or has an error.
     */
    public function testGetMessage()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $foundCoupon = $this->laracart->findCoupon('10OFF');
        $this->assertEquals('Coupon Applied', $foundCoupon->getMessage());

        $this->app['config']->set('laracart.coupon_applied_message', 'Your coupon has been applied');

        $this->assertEquals(true, $foundCoupon->canApply());
        $this->assertNull($foundCoupon->getFailedMessage());

        $this->assertEquals('Your coupon has been applied', $foundCoupon->getMessage());
    }

    /**
     * Test the min amount for a coupon.
     */
    public function testCheckMinAmount()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10, [
            'addOptions' => 1,
        ]);

        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals(true, $coupon->checkMinAmount(0));
        $this->assertEquals(false, $coupon->checkMinAmount(100, false));
        $this->assertEquals(1, $coupon->addOptions);
        try {
            $coupon->checkMinAmount(100);
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('You must have at least a total of $100.00', $e->getMessage());
        }
    }

    /**
     * Test the max discount for a coupon.
     */
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
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('This has a max discount of $10.00', $e->getMessage());
        }
    }

    /**
     * Test the valid times for a coupon.
     */
    public function testCheckValidTimes()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals(true, $coupon->checkValidTimes(Carbon::yesterday(), Carbon::tomorrow()));
        $this->assertEquals(false, $coupon->checkValidTimes(Carbon::tomorrow(), Carbon::tomorrow(), false));

        try {
            $this->assertEquals(false, $coupon->checkValidTimes(Carbon::tomorrow(), Carbon::tomorrow()));
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('This coupon has expired', $e->getMessage());
        }
    }

    /**
     * Test if we can set a coupon on an item.
     */
    public function testSetDiscountOnItem()
    {
        $item = $this->addItem(2, 30);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $this->assertEquals('53.50', $this->laracart->total(false));

        $coupon->setDiscountOnItem($item, 10.00);

        $this->assertEquals('10OFF', $item->code);

        try {
            $coupon->setDiscountOnItem($item, 'abc');
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidPrice::class);
        } catch (\LukePOLO\LaraCart\Exceptions\InvalidPrice $e) {
            $this->assertEquals('You must use a discount amount.', $e->getMessage());
        }

        $item = $this->addItem();

        $this->assertEquals('54.57', $this->laracart->total(false));

        $this->app['config']->set('laracart.discountTaxable', false);
        $this->assertEquals('55.27', $this->laracart->total(false));

        $this->laracart->removeCoupon('10OFF');

        $this->assertEquals('65.27', $this->laracart->total(false));

        $this->assertNull($item->code);
        $this->assertEquals(0, $item->discount);
        $this->assertCount(0, $item->couponInfo);
    }

    /**
     * Test cart percentage coupon when items are not taxable.
     */
    public function testCouponsNotTaxableItem()
    {
        $this->addItem(1, 1, false);

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('20%OFF', '.2');

        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('20%OFF'));

        $this->assertEquals('20%', $percentCoupon->displayValue());
        $this->assertEquals('0.20', $percentCoupon->discount());

        $this->app['config']->set('laracart.discountTaxable', false);

        $this->assertEquals(0, $this->laracart->taxTotal(false));

        $this->assertEquals('0.80', $this->laracart->total(false));
    }

    /**
     * Test cart percentage coupon when items are taxable.
     */
    public function testCouponsTaxableItem()
    {
        $this->addItem(1, 1);

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('20%OFF', '.2');

        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('20%OFF'));

        $this->assertEquals('20%', $percentCoupon->displayValue());
        $this->assertEquals('0.20', $percentCoupon->discount());

        $this->app['config']->set('laracart.discountTaxable', false);

        $this->assertEquals('0.07', $this->laracart->taxTotal(false));

        $this->assertEquals('0.87', $this->laracart->total(false));
    }

    /**
     * Test if we can remove all coupons from the cart.
     */
    public function testRemoveCoupons()
    {
        $item = $this->addItem(2, 30);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $coupon->setDiscountOnItem($item, 10.00);

        $this->assertEquals('10OFF', $item->code);

        $this->laracart->removeCoupons();

        $this->assertEmpty($this->laracart->getCoupons());

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));

        $this->laracart->removeCoupons();

        $this->assertEmpty($this->laracart->findCoupon('10OFF'));

        $cart = $this->laracart->get()->cart;
        $cart->multipleCoupons = true;

        $fixedCouponOne = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $fixedCouponTwo = new LukePOLO\LaraCart\Coupons\Fixed('5OFF', 5);

        $this->laracart->addCoupon($fixedCouponOne);
        $this->laracart->addCoupon($fixedCouponTwo);

        $this->assertCount(2, $this->laracart->getCoupons());

        $this->laracart->removeCoupons();

        $this->assertEmpty($this->laracart->getCoupons());
    }

    /**
     *  Testing getting the message on a coupon.
     */
    public function testCouponMessage()
    {
        $item = $this->addItem(2, 30);

        $fixedCoupon = new \LukePOLO\LaraCart\Tests\Coupons\Fixed('10OFF', 10);

        $this->assertEquals(false, $fixedCoupon->canApply());
        $this->assertEquals('Sorry, you must have at least 100 dollars!', $fixedCoupon->getMessage());
        $this->assertEquals('Sorry, you must have at least 100 dollars!', $fixedCoupon->getFailedMessage());
    }
}
