#Coupons 

<a name="overview"></a>
## Overview
Adding coupons could never be easier, currently there are a set of coupons inside LaraCart coupon folder. To create new types of coupons just create a copy of one of the existing coupons and modify it!

Coupon Type | Description
--- | --- | ---
Fixed Amount | Takes a fixed amount off the carts sub total | LukePOLO\LaraCart\Coupons\Fixed
Percentage | Takes a percentage off of the carts sub total | LukePOLO\LaraCart\Coupons\Percentage

<a name="helpers"></a>
## Coupon Helpers

The coupons include a `CouponTrait` that has some helpers, in which these
should be used within your coupon classes

    // Checks the minimum subtotal needed to apply the coupon
    $this->checkMinAmount($minAmount, $throwErrors = true)
    
    // Returns either the max discount or the discount applied based on what is passed through
    $this->maxDiscount($maxDiscount, $discount, $throwErrors = true)
    
    // Checks to see if the times are valid for the coupon
    $this->checkValidTimes(Carbon $startDate, Carbon $endDate, $throwErrors = true)
    
    // Sets a discount to an item with what code was used and the discount amount
    $this->setDiscountOnItem(CartItem $item, $discountAmount)</code></pre>

> {alert} Take a look at <a href="#custom-coupons">Custom Coupons</a> to see how to use these in your coupon

<a name="implantation"></a>
## Coupon Implantation
    
    $coupon = new \LukePOLO\LaraCart\Coupons\Fixed($coupon->CouponCode, $coupon->CouponValue, [
        'description' => $coupon->Description
    ]);
    
    LaraCart::addCoupon($coupon);
    LaraCart::removeCoupon($couponCode);
    LaraCart::removeCoupons();
    
    $fixedCoupon->getValue(); // $2.50
    $percentCoupon->getValue; // 15%
    
    $fixedCoupon->canApply(); // true or false
    $fixedCoupon->getFailedMessage(); // ex : 'you must have $10 in the cart!'
    
<a name="custom"></a>
## Custom Coupons
To create a custom coupon to fit your needs its pretty simple, first create a new file with these three functions :
    
    namespace App\Coupons;
    
    use LukePOLO\LaraCart\Contracts\CouponContract;
    use LukePOLO\LaraCart\Traits\CouponTrait;
    
    /**
     * Class MyCustomCoupon
     *
     * @package App\Coupons
     */
    class MyCustomCoupon implements CouponContract
    {
        use CouponTrait;
    
        /**
         * @param $code
         * @param $value
         */
        public function __construct($code, $value, $options = [])
        {
            $this->code = $code;
            $this->value = $value / 100;
    
            // this allows you to access your variables via $this->$option
            $this->setOptions($options);
        }
    
         /**
         * Gets the discount amount
         *
         * @param $throwErrors this allows us to capture errors in our code if we wish,
         * that way we can spit out why the coupon has failed
         *
         * @return string
         */
        public function discount($throwErrors = false)
        {
            // $this->minAmount was passed to the $options when constructing the coupon class
            $this->checkMinAmount($this->minAmount, $throwErrors)
            return \LaraCart::subTotal(false) * $this->value;
        }
    
        /**
         * Displays the type of value it is for the user
         *
         * @return mixed
         */
        public function displayValue($locale = null, $internationalFormat = null)
        {
            return \LaraCart::formatMoney($this->value, $locale, $internationalFormat);
        }
    }
    
Once you done this , you can easily use your coupon :
    
    $coupon = new App\MyCustomCoupon('10%OFF', '.10', [
        'description' => '10 % Off Any Purchase!'
    ]);
    
<a name="fees"></a>
## Fees
Fees allow you to add extra charges to the cart for various reasons like delivery fees, service charges or any other fee that you require.
    
    LaraCart::addFee('deliveryFee', 5, $taxable =  false, $options = []);
    LaraCart::removeFee('deliveryFee');