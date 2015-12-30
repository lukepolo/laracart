<?php

class TotalsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

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
        $this->addItem();

        $this->assertEquals("$0.07", $this->laracart->taxTotal());
        $this->assertEquals("0.07", $this->laracart->taxTotal(false));
    }

    public function testFeeTotals()
    {
        $this->laracart->addFee('test', 5);
        $this->laracart->addFee('test_2', 20);

        $this->assertEquals('$25.00', $this->laracart->feeTotals());
        $this->assertEquals(25, $this->laracart->feeTotals(false));

    }

    public function testSubTotal()
    {
        $item = $this->addItem(1, 24);

        $this->assertEquals('$24.00', $this->laracart->subTotal());
        $this->assertEquals('24.00', $this->laracart->subTotal(false, false));

        $item->qty = 5;

        $this->assertEquals('120.00', $this->laracart->subTotal(false, false));
    }

    public function testTotal()
    {
        $this->addItem();

        $this->assertEquals('$1.07', $this->laracart->total());
        $this->assertEquals('1.07', $this->laracart->total(false));


        $item = $this->addItem(1, 2, false);

        $this->assertEquals('$3.07', $this->laracart->total());
        $this->assertEquals('3.07', $this->laracart->total(false));

        $item->qty = 5;

        $this->assertEquals('11.00', $this->laracart->subTotal(false, false));
        $this->assertEquals('11.07', $this->laracart->total(false));
    }

    public function testTaxableFees()
    {

    }

    public function testTaxableItems()
    {

    }

    public function testTaxableSubItems()
    {

    }
}
