<?php

use Carbon\Carbon;

/**
 * Class CouponsTest.
 */
class CouponsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testInvalidPercentageCoupon()
    {
        $this->addItem(3, 1);

        try {
            $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '23');
            $this->expectException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('Invalid value for a percentage coupon. The value must be between 0 and 1.', $e->getMessage());
        }
    }

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
        $this->assertEquals('0.30', $this->laracart->discountTotal(false));

        $this->assertCount(1, $this->laracart->getCoupons());

        $this->assertEquals(3, $this->laracart->subTotal(false));
        $this->assertEquals(.19, $this->laracart->taxTotal(false));
    }

    /**
     * Test the percentage coupons on item with tax.
     */
    public function testAddPercentageCouponOnTaxItem()
    {
        $item = $this->addItem(1, 10);

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10%OFF', '.1');

        $this->laracart->addCoupon($percentCoupon);
        $percentCoupon->setDiscountOnItem($item);

        $this->assertEquals($percentCoupon, $this->laracart->findCoupon('10%OFF'));

        $this->assertEquals('10%', $percentCoupon->displayValue());
        $this->assertEquals(1, $percentCoupon->discount($item->price));
        $this->assertEquals(.63, $this->laracart->taxTotal(false));
        $this->assertEquals(9.63, $this->laracart->total(false));

        $this->assertCount(1, $this->laracart->getCoupons());
    }

    /**
     * Test the fixed coupons.
     */
    public function testAddFixedCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);
        $this->addItem(1, 20);
        $this->assertEquals('10.00', $this->laracart->discountTotal(false));
        $this->assertEquals('0.70', $this->laracart->taxTotal(false));
    }

    /**
     * Test the fixed coupons.
     */
    public function testFixedCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);
        $this->assertEquals($fixedCoupon, $this->laracart->findCoupon('10OFF'));
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
        $this->addItem();
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);
        $this->laracart->addCoupon($fixedCoupon);

        $foundCoupon = $this->laracart->findCoupon('10OFF');
        $this->assertEquals('Coupon Applied', $foundCoupon->getMessage());
        $this->assertEquals(true, $foundCoupon->canApply());
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
            $this->expectException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
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
            $this->expectException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
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
            $this->expectException(\LukePOLO\LaraCart\Exceptions\CouponException::class);
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('This coupon has expired', $e->getMessage());
        }
    }

    /**
     * Test if we can set a coupon on an item.
     */
    public function testSetDiscountOnItem()
    {
        $item = $this->addItem(1, 100);

        $this->assertEquals(107, $this->laracart->total(false));

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $fixedCoupon->setDiscountOnItem($item);

        $this->assertNotNull($item->coupon);

        $this->assertEquals('10OFF', $item->coupon->code);
        $this->assertEqualsWithDelta(90 * 1.07, $this->laracart->total(false), 0.0001);

        $this->laracart->removeCoupon('10OFF');

        $this->assertNull($item->coupon);
        $this->assertEquals(0, $item->discount);

        $this->assertEquals(0, $this->laracart->discountTotal(false));
        $this->assertEquals(107, $this->laracart->total(false));
    }

    public function testDiscountsTaxable()
    {
        $this->addItem(1, 20);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $this->laracart->findCoupon('10OFF');

        $this->assertEquals(20, $this->laracart->subTotal(false));

        $this->assertEquals(10 + (10 * .07), $this->laracart->total(false));
    }

    /**
     * Test if we can set a coupon on an item.
     */
    public function testDiscountTotals()
    {
        $item = $this->addItem(1, 10);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $coupon = $this->laracart->findCoupon('10OFF');

        $coupon->setDiscountOnItem($item);

        $this->assertEquals('10OFF', $item->coupon->code);

        $this->assertEquals(0, $this->laracart->total(false));
        $this->assertEquals(10, $this->laracart->discountTotal(false));
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
        $this->assertEquals('0.20', $this->laracart->discountTotal(false));

        $this->assertEquals(0, $this->laracart->taxTotal(false));

        $this->assertEquals('0.80', $this->laracart->total(false));
    }

    /**
     * Test cart percentage coupon when items are taxable.
     */
    public function testCouponsTaxableItem()
    {
        $this->addItem();

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('20%OFF', '.2');

        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals('20%', $percentCoupon->displayValue());
        $this->assertEquals('0.20', $this->laracart->discountTotal(false));

        $this->assertEquals('0.06', $this->laracart->taxTotal(false));
        $this->assertEquals('0.86', $this->laracart->total(false));
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

        $coupon->setDiscountOnItem($item);

        $this->assertEquals('10OFF', $item->coupon->code);

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

        try {
            $this->assertNotEquals(true, $fixedCoupon->discount($item->price));
        } catch (\LukePOLO\LaraCart\Exceptions\CouponException $e) {
            $this->assertEquals('Sorry, you must have at least 100 dollars!', $e->getMessage());
        }
    }

    /**
     * Testing discount when total is greater than applied coupon value.
     */
    public function testFixedCouponWithTotalLessThanCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('500 OFF', 500);

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals('0.00', $this->laracart->discountTotal(false));

        $this->addItem(1, 400);

        $this->assertEquals('400.00', $this->laracart->discountTotal(false));
    }

    /**
     * Testing discount when total with fees is greater than applied coupon value.
     */
    public function testFixedCouponWithFeeWithTotalLessThanCoupon()
    {
        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('500 OFF', 500);

        $this->laracart->addCoupon($fixedCoupon);

        $this->addItem(1, 400);

        $this->laracart->addFee('testFee', 150);

        $this->assertEquals('400.00', $this->laracart->discountTotal(false));

        $this->assertEquals(150, $this->laracart->total(false));

        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('100% Off', 1);
        $this->laracart->addCoupon($percentCoupon);

        $this->assertEquals(150, $this->laracart->total(false));
    }

    public function testFeeDiscount()
    {
        $this->app['config']->set('laracart.discount_fees', true);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('10 OFF', 10);

        $this->laracart->addCoupon($fixedCoupon);

        $this->addItem(1, 5);

        $this->laracart->addFee('testFee', 15);

        $this->assertEquals(10, $this->laracart->total(false));
    }

    /**
     * Testing percentage coupon on multiple item qty.
     */
    public function testPercentageCouponOnMultipleQtyItems()
    {
        $percentCoupon = new LukePOLO\LaraCart\Coupons\Percentage('10% Off', .1);

        $item = $this->addItem(2, 10);

        $percentCoupon->setDiscountOnItem($item);

        $this->assertEquals(18, $this->laracart->total(false) - $this->laracart->taxTotal(false));
    }

    /**
     * Testing Discount Pre Taxed.
     */
    public function testPreTaxDiscountFixed()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.fees_taxable', false);

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('$1 Off', 1);

        $this->addItem(1, .84);

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals(0, $this->laracart->total(false));
    }

    /**
     * Testing Discount Pre Taxed.
     */
    public function testPreTaxDiscountPercentage()
    {
        $this->app['config']->set('laracart.tax', .19);
        $this->app['config']->set('laracart.fees_taxable', false);

        $percentageCoupon = new LukePOLO\LaraCart\Coupons\Percentage('100%', 1);

        $this->addItem(1, .84);

        $this->laracart->addCoupon($percentageCoupon);

        $this->assertEquals(0, $this->laracart->total(false));
    }

    public function testCartVsItemCoupon()
    {
        $item = $this->addItem();
        $couponPercentage = new \LukePOLO\LaraCart\Coupons\Percentage('50%', 0.5);
        $this->laracart->addCoupon($couponPercentage);

        $cartTotal = $this->laracart->total(false);
        $this->laracart->removeCoupon($couponPercentage->code);

        $this->assertEquals(1.07, $this->laracart->total(false));

        $item->addCoupon($couponPercentage);

        $itemTotal = $this->laracart->total(false);

        $this->assertEquals(.54, $cartTotal);
        $this->assertEquals($itemTotal, $cartTotal);
    }

    public function testCouponOnSubItems()
    {
        $item = $this->addItem(1, 0);

        $item->addSubItem([
            'size'  => 'XXL',
            'price' => 5,
        ]);

        $this->assertEquals(5, $this->laracart->subTotal(false));

        $fixedCoupon = new LukePOLO\LaraCart\Coupons\Fixed('5 OFF', 5);

        $this->laracart->addCoupon($fixedCoupon);

        $this->assertEquals(0, $this->laracart->total(false));
    }
}
