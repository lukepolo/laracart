<?php

namespace LukePOLO\LaraCart;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Session\SessionManager;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Contracts\LaraCartContract;
use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class LaraCart.
 */
class LaraCart implements LaraCartContract
{
    const SERVICE = 'laracart';
    const HASH = LaraCartHasher::class;
    const RANHASH = 'generateRandomCartItemHash';

    protected $events;
    protected $session;
    protected $authManager;

    public $cart;
    public $prefix;
    public $itemModel;
    public $itemModelRelations;

    /**
     * LaraCart constructor.
     *
     * @param SessionManager $session
     * @param Dispatcher     $events
     * @param AuthManager    $authManager
     */
    public function __construct(SessionManager $session, Dispatcher $events, AuthManager $authManager)
    {
        $this->session = $session;
        $this->events = $events;
        $this->authManager = $authManager->guard(config('laracart.guard', null));
        $this->prefix = config('laracart.cache_prefix', 'laracart');
        $this->itemModel = config('laracart.item_model', null);
        $this->itemModelRelations = config('laracart.item_model_relations', []);

        $this->setInstance($this->session->get($this->prefix.'.instance', 'default'));
    }

    /**
     * Gets all current instances inside the session.
     *
     * @return mixed
     */
    public function getInstances()
    {
        return $this->session->get($this->prefix.'.instances', []);
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using.
     *
     * @param string $instance
     *
     * @return LaraCart
     */
    public function setInstance($instance = 'default')
    {
        $this->get($instance);

        $this->session->put($this->prefix.'.instance', $instance);

        if (!in_array($instance, $this->getInstances())) {
            $this->session->push($this->prefix.'.instances', $instance);
        }
        $this->events->dispatch('laracart.new');

        return $this;
    }

    /**
     * Gets the instance in the session.
     *
     * @param string $instance
     *
     * @return $this cart instance
     */
    public function get($instance = 'default')
    {
        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            if (!empty($cartSessionID = $this->authManager->user()->cart_session_id)) {
                $this->session->setId($cartSessionID);
                $this->session->start();
            }
        }

        if (empty($this->cart = $this->session->get($this->prefix.'.'.$instance))) {
            $this->cart = new Cart($instance);
        }

        return $this;
    }

    /**
     * Gets an an attribute from the cart.
     *
     * @param $attribute
     * @param $defaultValue
     *
     * @return mixed
     */
    public function getAttribute($attribute, $defaultValue = null)
    {
        return array_get($this->cart->attributes, $attribute, $defaultValue);
    }

    /**
     * Gets all the carts attributes.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->cart->attributes;
    }

    /**
     * Adds an Attribute to the cart.
     *
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value)
    {
        array_set($this->cart->attributes, $attribute, $value);

        $this->update();
    }

    /**
     * Updates cart session.
     */
    public function update()
    {
        $this->session->put($this->prefix.'.'.$this->cart->instance, $this->cart);

        if (config('laracart.cross_devices', false) && $this->authManager->check()) {
            $this->authManager->user()->cart_session_id = $this->session->getId();
            $this->authManager->user()->save();
        }

        $this->session->reflash();

        $this->session->save();

        $this->events->dispatch('laracart.update', $this->cart);
    }

    /**
     * Removes an attribute from the cart.
     *
     * @param $attribute
     */
    public function removeAttribute($attribute)
    {
        array_forget($this->cart->attributes, $attribute);

        $this->update();
    }

    /**
     * Creates a CartItem and then adds it to cart.
     *
     * @param string|int $itemID
     * @param null       $name
     * @param int        $qty
     * @param string     $price
     * @param array      $options
     * @param bool|true  $taxable
     *
     * @throws ModelNotFound
     *
     * @return CartItem
     */
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', $options = [], $taxable = true)
    {
        return $this->add($itemID, $name, $qty, $price, $options, $taxable, true);
    }

    /**
     * Creates a CartItem and then adds it to cart.
     *
     * @param $itemID
     * @param null       $name
     * @param int        $qty
     * @param string     $price
     * @param array      $options
     * @param bool|false $taxable
     * @param bool|false $lineItem
     *
     * @throws ModelNotFound
     *
     * @return CartItem
     */
    public function add(
        $itemID,
        $name = null,
        $qty = 1,
        $price = '0.00',
        $options = [],
        $taxable = true,
        $lineItem = false
    ) {
        if (!empty(config('laracart.item_model'))) {
            $itemModel = $itemID;

            if (!$this->isItemModel($itemModel)) {
                $itemModel = (new $this->itemModel())->with($this->itemModelRelations)->find($itemID);
            }

            if (empty($itemModel)) {
                throw new ModelNotFound('Could not find the item '.$itemID);
            }

            $bindings = config('laracart.item_model_bindings');

            $itemID = $itemModel[$bindings[\LukePOLO\LaraCart\CartItem::ITEM_ID]];

            if (is_int($name)) {
                $qty = $name;
            }

            $name = $itemModel[$bindings[\LukePOLO\LaraCart\CartItem::ITEM_NAME]];
            $price = $itemModel[$bindings[\LukePOLO\LaraCart\CartItem::ITEM_PRICE]];

            $options['model'] = $itemModel;

            $options = array_merge($options,
                $this->getItemModelOptions($itemModel, $bindings[\LukePOLO\LaraCart\CartItem::ITEM_OPTIONS]));

            $taxable = $itemModel[$bindings[\LukePOLO\LaraCart\CartItem::ITEM_TAXABLE]] ? true : false;
        }

        $item = $this->addItem(new CartItem(
            $itemID,
            $name,
            $qty,
            $price,
            $options,
            $taxable,
            $lineItem
        ));

        $this->update();

        return $this->getItem($item->getHash());
    }

    /**
     * Adds the cartItem into the cart session.
     *
     * @param CartItem $cartItem
     *
     * @return CartItem
     */
    public function addItem(CartItem $cartItem)
    {
        $itemHash = $cartItem->generateHash();

        if ($this->getItem($itemHash)) {
            $this->getItem($itemHash)->qty += $cartItem->qty;
        } else {
            $this->cart->items[] = $cartItem;
        }

        $this->events->dispatch('laracart.addItem', $cartItem);

        return $cartItem;
    }

    /**
     * Increment the quantity of a cartItem based on the itemHash.
     *
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function increment($itemHash)
    {
        $item = $this->getItem($itemHash);
        $item->qty++;
        $this->update();

        return $item;
    }

    /**
     * Decrement the quantity of a cartItem based on the itemHash.
     *
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function decrement($itemHash)
    {
        $item = $this->getItem($itemHash);
        if ($item->qty > 1) {
            $item->qty--;
            $this->update();

            return $item;
        }
        $this->removeItem($itemHash);
        $this->update();
    }

    /**
     * Find items in the cart matching a data set.
     *
     * param $data
     *
     * @return array | CartItem | null
     */
    public function find($data)
    {
        $matches = [];

        foreach ($this->getItems() as $item) {
            if ($item->find($data)) {
                $matches[] = $item;
            }
        }

        switch (count($matches)) {
            case 0:
                return;
                break;
            case 1:
                return $matches[0];
                break;
            default:
                return $matches;
        }
    }

    /**
     * Finds a cartItem based on the itemHash.
     *
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function getItem($itemHash)
    {
        return array_get($this->getItems(), $itemHash);
    }

    /**
     * Gets all the items within the cart.
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];
        if (isset($this->cart->items) === true) {
            foreach ($this->cart->items as $item) {
                $items[$item->getHash()] = $item;
            }
        }

        return $items;
    }

    /**
     * Updates an items attributes.
     *
     * @param $itemHash
     * @param $key
     * @param $value
     *
     * @return CartItem|null
     */
    public function updateItem($itemHash, $key, $value)
    {
        if (empty($item = $this->getItem($itemHash)) === false) {
            if ($key == 'qty' && $value == 0) {
                return $this->removeItem($itemHash);
            }

            $item->$key = $value;
        }

        $this->update();

        return $item;
    }

    /**
     * Removes a CartItem based on the itemHash.
     *
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        if (empty($this->cart->items) === false) {
            foreach ($this->cart->items as $itemKey => $item) {
                if ($item->getHash() == $itemHash) {
                    unset($this->cart->items[$itemKey]);
                    break;
                }
            }

            $this->events->dispatch('laracart.removeItem', $item);

            $this->update();
        }
    }

    /**
     * Empties the carts items.
     */
    public function emptyCart()
    {
        unset($this->cart->items);

        $this->update();

        $this->events->dispatch('laracart.empty', $this->cart->instance);
    }

    /**
     * Completely destroys cart and anything associated with it.
     */
    public function destroyCart()
    {
        $instance = $this->cart->instance;

        $this->session->forget($this->prefix.'.'.$instance);

        $this->events->dispatch('laracart.destroy', $instance);

        $this->cart = new Cart($instance);

        $this->update();
    }

    /**
     * Gets the coupons for the current cart.
     *
     * @return array
     */
    public function getCoupons()
    {
        return $this->cart->coupons;
    }

    /**
     * Finds a specific coupon in the cart.
     *
     * @param $code
     *
     * @return mixed
     */
    public function findCoupon($code)
    {
        return array_get($this->cart->coupons, $code);
    }

    /**
     * Applies a coupon to the cart.
     *
     * @param CouponContract $coupon
     */
    public function addCoupon(CouponContract $coupon)
    {
        if (!$this->cart->multipleCoupons) {
            $this->cart->coupons = [];
        }

        $this->cart->coupons[$coupon->code] = $coupon;

        $this->update();
    }

    /**
     * Removes a coupon in the cart.
     *
     * @param $code
     */
    public function removeCoupon($code)
    {
        $this->removeCouponFromItems($code);
        array_forget($this->cart->coupons, $code);
        $this->update();
    }

    /**
     * Removes all coupons from the cart.
     */
    public function removeCoupons()
    {
        $this->removeCouponFromItems();
        $this->cart->coupons = [];
        $this->update();
    }

    /**
     * Gets a specific fee from the fees array.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getFee($name)
    {
        return array_get($this->cart->fees, $name, new CartFee(null, false));
    }

    /**
     * Allows to charge for additional fees that may or may not be taxable
     * ex - service fee , delivery fee, tips.
     *
     * @param $name
     * @param $amount
     * @param bool|false $taxable
     * @param array      $options
     */
    public function addFee($name, $amount, $taxable = false, array $options = [])
    {
        array_set($this->cart->fees, $name, new CartFee($amount, $taxable, $options));

        $this->update();
    }

    /**
     * Removes a fee from the fee array.
     *
     * @param $name
     */
    public function removeFee($name)
    {
        array_forget($this->cart->fees, $name);

        $this->update();
    }

    /**
     * Removes all the fees set in the cart.
     */
    public function removeFees()
    {
        $this->cart->fees = [];

        $this->update();
    }

    /**
     * Gets the total tax for the cart.
     *
     * @param bool|true $format
     * @param bool|true $withFees
     * @param bool      $grossTaxes
     *
     * @return string
     */
    public function taxTotal($format = true, $withFees = true, $grossTaxes = true)
    {
        $totalTax = 0;
        $discounted = 0;
        $totalDiscount = $this->totalDiscount(false, false);

        if (config('laracart.discountsAlreadyTaxed', false)) {
            $totalDiscount = 0;
        }

        if ($this->count() != 0) {
            /**
             * @var
             * @var CartItem $item
             */
            foreach ($this->getItems() as $index => $item) {
                if ($discounted >= $totalDiscount) {
                    $totalTax += $item->tax(null, $grossTaxes);
                } else {
                    $itemPrice = $item->subTotal(false, config('laracart.discountTaxable', false));
                    if (($discounted + $itemPrice) > $totalDiscount) {
                        $totalTax += config('laracart.discountTaxable', false) ? $item->tax() : $item->tax($totalDiscount - $discounted);
                    }

                    $discounted += $itemPrice;
                }
            }
        }

        if ($withFees) {
            foreach ($this->getFees() as $fee) {
                if ($fee->taxable) {
                    $totalTax += $fee->amount * $fee->tax;
                }
            }
        }

        return $this->formatMoney($totalTax, null, null, $format);
    }

    /**
     * Gets the total of the cart with or without tax.
     *
     * @param bool $format
     * @param bool $withDiscount
     * @param bool $withTax
     * @param bool $withFees
     *
     * @return string
     */
    public function total($format = true, $withDiscount = true, $withTax = true, $withFees = true)
    {
        $total = $this->subTotal(false);

        if ($withFees) {
            $total += $this->feeTotals(false);
        }

        if ($withDiscount) {
            $total -= $this->totalDiscount(false, false);
        }

        if ($withTax) {
            $total += $this->taxTotal(false, $withFees, false);
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Gets the subtotal of the cart with or without tax.
     *
     * @param bool $format
     * @param bool $withDiscount
     *
     * @return string
     */
    public function subTotal($format = true, $withDiscount = true)
    {
        $total = 0;

        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal(false, $withDiscount);
            }
        }

        if ($total < 0) {
            $total = 0;
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Gets the netTotal of the cart.
     *
     * @param bool $format
     *
     * @return string
     */
    public function netTotal($format = true)
    {
        $total = 0;

        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->netTotal(false);
            }
        }

        if ($total < 0) {
            $total = 0;
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Get the count based on qty, or number of unique items.
     *
     * @param bool $withItemQty
     *
     * @return int
     */
    public function count($withItemQty = true)
    {
        $count = 0;

        foreach ($this->getItems() as $item) {
            if ($withItemQty) {
                $count += $item->qty;
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Formats the number into a money format based on the locale and international formats.
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     * @param $format
     *
     * @return string
     */
    public static function formatMoney($number, $locale = null, $internationalFormat = false, $format = true)
    {
        $number = number_format($number, 2, '.', '');

        if ($format) {
            setlocale(LC_MONETARY, null);
            setlocale(LC_MONETARY, empty($locale) ? config('laracart.locale', 'en_US.UTF-8') : $locale);

            if (empty($internationalFormat) === true) {
                $internationalFormat = config('laracart.international_format', false);
            }

            $number = money_format($internationalFormat ? '%i' : '%n', $number);
        }

        return $number;
    }

    /**
     * Gets all the fee totals.
     *
     * @param bool $format
     * @param bool $withTax
     *
     * @return string
     */
    public function feeTotals($format = true, $withTax = false)
    {
        $feeTotal = 0;

        foreach ($this->getFees() as $fee) {
            $feeTotal += $fee->amount;

            if ($withTax && $fee->taxable && $fee->tax > 0) {
                $feeTotal += $fee->amount * $fee->tax;
            }
        }

        return $this->formatMoney($feeTotal, null, null, $format);
    }

    /**
     * Gets all the fees on the cart object.
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->cart->fees;
    }

    /**
     * Gets the total amount discounted.
     *
     * @param bool $format
     * @param bool $withItemDiscounts
     *
     * @return string
     */
    public function totalDiscount($format = true, $withItemDiscounts = true)
    {
        $total = 0;

        if ($withItemDiscounts) {
            /** @var CartItem $item */
            foreach ((array) $this->cart->items as $item) {
                $total += floatval($item->getDiscount(false));
            }
        }

        foreach ($this->cart->coupons as $coupon) {
            if ($coupon->appliedToCart) {
                $total += $coupon->discount();
            }
        }

        if ($total < 0) {
            $total = $this->subTotal(false);
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Checks to see if its an item model.
     *
     * @param $itemModel
     *
     * @return bool
     */
    private function isItemModel($itemModel)
    {
        if (is_object($itemModel) && get_class($itemModel) == config('laracart.item_model')) {
            return true;
        }

        return false;
    }

    /**
     * Gets the item models options based the config.
     *
     * @param Model $itemModel
     * @param array $options
     *
     * @return array
     */
    private function getItemModelOptions(Model $itemModel, $options = [])
    {
        $itemOptions = [];
        foreach ($options as $option) {
            $itemOptions[$option] = $this->getFromModel($itemModel, $option);
        }

        return array_filter($itemOptions, function ($value) {
            if ($value !== false && empty($value)) {
                return false;
            }

            return true;
        });
    }

    /**
     * Gets a option from the model.
     *
     * @param Model $itemModel
     * @param $attr
     * @param null $defaultValue
     *
     * @return Model|null
     */
    private function getFromModel(Model $itemModel, $attr, $defaultValue = null)
    {
        $variable = $itemModel;

        if (!empty($attr)) {
            foreach (explode('.', $attr) as $attr) {
                $variable = array_get($variable, $attr, $defaultValue);
            }
        }

        return $variable;
    }

    /**
     * Removes a coupon from the item.
     *
     * @param null $code
     */
    private function removeCouponFromItems($code = null)
    {
        foreach ($this->getItems() as $item) {
            if (isset($item->code) && (empty($code) || $item->code == $code)) {
                $item->code = null;
                $item->coupon = null;
            }
        }
    }
}
