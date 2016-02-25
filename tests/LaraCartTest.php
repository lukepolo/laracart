<?php

/**
 * Class LaraCartTest
 */
class LaraCartTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test getting the laracart instance
     */
    public function testGetInstance()
    {
        $this->assertEquals(new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager),
            $this->laracart->get());
    }

    /**
     * Test setting the instance
     */
    public function testSetInstance()
    {
        $this->assertNotEquals(new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager),
            $this->laracart->setInstance('test'));
    }

    /**
     * Test to make sure we get default instance
     */
    public function testGetInstancesDefault()
    {
        $this->assertEquals('default', $this->laracart->getInstances()[0]);
    }

    /**
     * Test to make sure we can get instances
     */
    public function testGetInstances()
    {
        $this->laracart->setInstance('test');
        $this->laracart->setInstance('test');
        $this->laracart->setInstance('test');
        $this->laracart->setInstance('test-2');
        $this->laracart->setInstance('test-3');

        $this->assertCount(4, $this->laracart->getInstances());
    }

    /**
     * Testing the money format function
     */
    public function testFormatMoney()
    {
        $this->assertEquals('$25.00', $this->laracart->formatMoney('25.00'));
        $this->assertEquals('USD 25.00', $this->laracart->formatMoney('25.00', null, true));
        $this->assertEquals('25.00', $this->laracart->formatMoney('25.00', null, null, false));

        $this->assertEquals('$25.56', $this->laracart->formatMoney('25.555'));
        $this->assertEquals('$25.54', $this->laracart->formatMoney('25.544'));
    }

    /**
     * Test getting the attributes from the cart
     */
    public function testGetAttributes()
    {
        $this->laracart->setAttribute('test1', 1);
        $this->laracart->setAttribute('test2', 2);

        $this->assertCount(2, $attributes = $this->laracart->getAttributes());

        $this->assertEquals(1, $attributes['test1']);
        $this->assertEquals(2, $attributes['test2']);
    }

    /**
     * Test removing attributes from the cart
     */
    public function testRemoveAttribute()
    {
        $this->laracart->setAttribute('test1', 1);

        $this->assertEquals(1, $this->laracart->getAttribute('test1'));

        $this->laracart->removeAttribute('test1');

        $this->assertNull($this->laracart->getAttribute('test1'));
    }

    /**
     * Testing if the item count matches
     */
    public function testCount()
    {
        $this->addItem(2);

        $this->assertEquals(2, $this->laracart->count());
        $this->assertEquals(1, $this->laracart->count(false));
    }

    /**
     * Makes sure that when we empty the cart it deletes all items
     */
    public function testEmptyCart()
    {
        $this->addItem();

        $this->laracart->setAttribute('test', 1);

        $this->laracart->emptyCart();

        $this->assertEquals(1, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    /**
     * Test destroying the cart rather than just emptying it
     */
    public function testDestroyCart()
    {
        $this->addItem();

        $this->laracart->setAttribute('test', 1);

        $this->laracart->destroyCart();

        $this->assertEquals(null, $this->laracart->getAttribute('test'));
        $this->assertEquals(0, $this->laracart->count());
    }

    /**
     * Testing to make sure if we switch carts and destroy it destroys the proper cart
     */
    public function testDestroyOtherCart()
    {
        $this->addItem();

        $this->laracart->setInstance('test');

        $this->addItem();

        $cart = $this->laracart->get('test');

        $this->assertEquals(1, $cart->count());

        $this->laracart->destroyCart();

        $cart = $this->laracart->get('test');

        $this->assertEquals(0, $cart->count());

        $cart = $this->laracart->get();

        $this->assertEquals(1, $cart->count());
    }

    /**
     * Tests if generating a new hash when we change an option
     */
    public function testGeneratingHashes()
    {

        $item = $this->addItem();

        $prevHash = $item->getHash();

        $item->name = 'NEW NAME';

        $this->assertNotEquals($prevHash, $item->getHash());
    }

}
