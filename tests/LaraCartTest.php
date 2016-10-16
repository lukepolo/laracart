<?php

/**
 * Class LaraCartTest.
 */
class LaraCartTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test getting the laracart instance.
     */
    public function testGetInstance()
    {
        $this->assertEquals(new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager),
            $this->app->make('laracart'));
    }

    /**
     * Test setting the instance.
     */
    public function testSetInstance()
    {
        $this->assertNotEquals(new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager),
            $this->laracart->instance('test'));
    }

    /**
     * Test to make sure we get default instance.
     */
    public function testGetInstancesDefault()
    {
        $this->assertEquals('default', $this->laracart->instance()->cart->instance);
    }

    /**
     * Test to make sure we can get instances.
     */
    public function testGetInstances()
    {
        $this->laracart->instance();
        $this->laracart->instance('test');
        $this->laracart->instance('test');
        $this->laracart->instance('test-2');
        $this->laracart->instance('test-3');

        $this->assertCount(4, $this->laracart->instances());
    }

    /**
     * Testing the money format function.
     */
    public function testFormatMoney()
    {
        $this->assertEquals('$25.00', $this->laracart->formatMoney('25.00'));
        $this->assertEquals('USD 25.00', $this->laracart->formatMoney('25.00', null, true));
        $this->assertEquals('25.00', $this->laracart->formatMoney('25.00')->amount());

        $this->assertEquals('$25.56', $this->laracart->formatMoney('25.555'));
        $this->assertEquals('$25.54', $this->laracart->formatMoney('25.544'));
    }

    /**
     * Test getting the attributes from the cart.
     */
    public function testgets()
    {
        $this->laracart->set('test1', 1);
        $this->laracart->set('test2', 2);

        $this->assertCount(2, $attributes = $this->laracart->attributes());

        $this->assertEquals(1, $attributes['test1']);
        $this->assertEquals(2, $attributes['test2']);
    }

    /**
     * Test removing attributes from the cart.
     */
    public function testRemoveAttribute()
    {
        $this->laracart->set('test1', 1);

        $this->assertEquals(1, $this->laracart->get('test1'));

        $this->laracart->remove('test1');

        $this->assertNull($this->laracart->get('test1'));
    }

    /**
     * Testing if the item count matches.
     */
    public function testCount()
    {
        $this->addItem(2);

        $this->assertEquals(2, $this->laracart->count());
    }

    /**
     * Makes sure that when we empty the cart it deletes all items.
     */
    public function testEmptyCart()
    {
        $this->addItem();

        $this->laracart->set('test', 1);

        $this->laracart->clear();

        $this->assertEquals(1, $this->laracart->get('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    /**
     * Test destroying the cart rather than just emptying it.
     */
    public function testDestroyCart()
    {
        $this->addItem();

        $this->laracart->set('test', 1);

        $this->laracart->destroy();

        $this->assertEquals(null, $this->laracart->get('test'));

        $this->assertEquals(0, $this->laracart->count());
    }

    /**
     * Testing to make sure if we switch carts and destroy it destroys the proper cart.
     */
    public function testDestroyOtherCart()
    {
        $this->addItem();

        $this->laracart->instance('test');

        $this->addItem();

        $cart = $this->laracart->instance('test');

        $this->assertEquals(1, $cart->count());


        $this->laracart->destroy();

        $cart = $this->laracart->instance('test');

        $this->assertEquals(0, $cart->count());

        $cart = $this->laracart->instance();

        $this->assertEquals(1, $cart->count());
    }

    /**
     * Tests if generating a new hash when we change an option.
     */
    public function testGeneratingHashes()
    {
        $item = $this->addItem();

        $prevHash = $item->hash();

        $item->name = 'NEW NAME';


        $this->assertNotEquals($prevHash, $item->hash());
    }

    /**
     * Tests the facade.
     */
    public function getFacadeName()
    {
        $facade = new \LukePOLO\LaraCart\Facades\LaraCart();
        $this->assertEquals('laracart', $facade::getFacadeAccessor());
    }
}
