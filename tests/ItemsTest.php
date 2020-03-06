<?php

use Illuminate\Support\Facades\Event;

/**
 * Class ItemsTest.
 */
class ItemsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test if we can add an item to the cart.
     */
    public function testAddItem()
    {
        Event::fake();

        $cartItem = $this->addItem();

        Event::assertDispatched('laracart.addItem', function ($e, $item) use ($cartItem) {
            return $item === $cartItem;
        });

        $this->assertEquals(1, Event::dispatched('laracart.addItem')->count());

        $this->addItem();

        $this->assertEquals(1, $this->laracart->count(false));
        $this->assertEquals(2, $this->laracart->count());
    }

    /**
     * Test if we can increment a quantity to an item.
     */
    public function testIncrementItem()
    {
        $item = $this->addItem();
        $itemHash = $item->getHash();
        $this->laracart->increment($itemHash);

        $this->assertEquals(2, $this->laracart->count());
    }

    /**
     * Test if we can decrement a quantity to an item.
     */
    public function testDecrementItem()
    {
        $item = $this->addItem();
        $itemHash = $item->getHash();
        $this->laracart->increment($itemHash);
        $this->laracart->decrement($itemHash);

        $this->assertEquals(1, $this->laracart->count());

        $this->laracart->decrement($itemHash);

        $this->assertEquals(0, $this->laracart->count());
    }

    /**
     * Test if we can decrement an item with a quantity of 1 (= delete item).
     */
    public function testDecrementUniqueItem()
    {
        $item = $this->addItem();
        $itemHash = $item->getHash();
        $this->laracart->decrement($itemHash);

        $this->assertEquals(null, $this->laracart->getItem($itemHash));
    }

    /**
     * Tests when we add multiples of the same item it updates the qty properly.
     */
    public function testItemQtyUpdate()
    {
        $item = $this->addItem();
        $itemHash = $item->getHash();
        $this->addItem();
        $this->addItem();
        $this->addItem();
        $this->addItem();
        $this->addItem();
        $this->addItem();

        $this->assertEquals(7, $item->qty);
        $this->assertEquals($itemHash, $item->getHash());

        $options = [
            'a' => 2,
            'b' => 1,
        ];

        $item = $this->addItem(1, 1, false, $options);
        $this->addItem(1, 1, false, array_reverse($options));

        $this->assertEquals(2, $item->qty);
    }

    /**
     * Test if we can add an line item to the cart.
     */
    public function testAddLineItem()
    {
        $this->addItem();
        $this->addItem();

        $this->laracart->addLine(
            'itemID',
            'Testing Item',
            1,
            '1',
            [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(2, $this->laracart->count(false));
        $this->assertEquals(3, $this->laracart->count());
    }

    /**
     * Test getting an item from the cart.
     */
    public function testGetItem()
    {
        $item = $this->addItem();
        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));
    }

    /**
     * Test updating the item.
     */
    public function testUpdateItem()
    {
        $item = $this->addItem();

        Event::fake();

        $this->laracart->updateItem($item->getHash(), 'qty', 4);

        Event::assertDispatched('laracart.updateItem', function ($e, $eventItem) use ($item) {
            return $eventItem['item'] === $item && $eventItem['newHash'] === $item->getHash();
        });

        $this->assertEquals(1, Event::dispatched('laracart.updateItem')->count());
        $this->assertEquals(4, $item->qty);
    }

    /**
     * Test getting all the items from the cart.
     */
    public function testGetItems()
    {
        $this->addItem();
        $this->addItem();

        $items = $this->laracart->getItems();

        $this->assertInternalType('array', $items);

        $this->assertCount(1, $items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $items);
    }

    /**
     * Test the price and qty based on the item.
     */
    public function testItemPriceAndQty()
    {
        $item = $this->addItem(3, 10);

        $this->assertEquals(3, $item->qty);
        $this->assertEquals(10, $item->price(false));
        $this->assertEquals(10.7, $item->price(false, false, true)); // return item price with tax
        $this->assertEquals(30, $item->subTotal(false));
        $this->assertEquals(32.1, $item->subTotal(false, true, false, true)); // return subtotal with tax
    }

    /**
     * Test the prices in cents based on the item.
     */
    public function testItemPriceInCents()
    {
        $this->app['config']->set('laracart.prices_in_cents', true);
        $item = $this->addItem(3, 1000);

        $this->assertEquals(1000, $item->price(false));
        $this->assertEquals(1070, $item->price(false, false, true)); // return item price with tax
        $this->assertEquals(3000, $item->subTotal(false));
        $this->assertEquals(3210, $item->subTotal(false, true, false, true)); // return subtotal with tax

        // Test that floats are converted to int and not rounded in the constructor
        $item2 = $this->addItem(3, 1000.55);
        $this->assertEquals(1000, $item2->price(false));
    }

    /**
     * Test removing an item from the cart.
     */
    public function testRemoveItem()
    {
        $item = $this->addItem();

        $this->laracart->removeItem($item->getHash());

        $this->assertEmpty($this->laracart->getItem($item->getHash()));
    }

    /**
     * Test seeing a valid and invalid price.
     */
    public function testSetPrice()
    {
        $item = $this->addItem();
        $item->price = 3;

        $this->assertEquals(3, $item->price);

        $item->price = 3.52313123;
        $this->assertEquals(3.52313123, $item->price);

        $item->price = -123123.000;
        $this->assertEquals(-123123.000, $item->price);

        try {
            $item->price = 'a';
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidPrice::class);
        } catch (\LukePOLO\LaraCart\Exceptions\InvalidPrice $e) {
            $this->assertEquals('The price must be a valid number', $e->getMessage());
        }
    }

    /**
     * Test seeing a valid and invalid qty.
     */
    public function testSetQty()
    {
        $item = $this->addItem();

        $item->qty = 3;
        $this->assertEquals(3, $item->qty);

        $item->qty = 1.5;
        $this->assertEquals(1.5, $item->qty);

        try {
            $item->qty = 'a';
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidPrice::class);
        } catch (\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }

        try {
            $item->qty = 'a';
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidQuantity::class);
        } catch (\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }

        try {
            $item->qty = -1;
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidQuantity::class);
        } catch (\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }
    }

    /**
     * Tests the different taxes on items.
     */
    public function testDifferentTaxes()
    {
        $item = $this->addItem();

        $prevHash = $item->getHash();

        $item->tax = .05;

        $this->assertNotEquals($prevHash, $item->getHash());

        $item = $this->addItem();
        $item->tax = .3;

        $this->assertEquals('2.35', $this->laracart->total(false));

        $item = $this->addItem(1, 1, true, [
            'tax' => .7,
        ]);

        $this->assertEquals('.70', $item->tax());

        $this->assertEquals('4.05', $this->laracart->total(false));
    }

    /**
     * Test that an item can be found by the value of an option.
     */
    public function testFindingAnItemByOptionSucceeds()
    {
        $item1 = $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value1',
        ]);

        $item2 = $this->addItem(1, 1, true, [
            'key1' => 'notmatching',
            'key2' => 'value2',
        ]);

        $result1 = $this->laracart->find(['key1' => 'matching']);
        $result2 = $this->laracart->find(['key2' => 'value2']);

        $this->assertEquals($item1->key1, $result1->key1);
        $this->assertEquals($item1->getHash(), $result1->getHash());

        $this->assertEquals($item2->key2, $result2->key2);
        $this->assertEquals($item2->getHash(), $result2->getHash());
    }

    /**
     * Test that an item is not found by the value of an option when it does not exist.
     */
    public function testFindingAnItemByOptionFails()
    {
        $this->addItem(1, 1, true, [
            'key1' => 'notmatching',
        ]);

        $this->addItem(1, 1, true, [
            'key2' => 'notmatching',
        ]);

        $this->assertNull($this->laracart->find(['key1' => 'matching']));
    }

    /**
     * Test that multiple matching items are found by the value of an option.
     */
    public function testFindingAnItemReturnsMultipleMatches()
    {
        $item1 = $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value1',
        ]);

        $item2 = $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value2',
        ]);

        $item3 = $this->addItem(1, 1, true, [
            'key1' => 'nomatch',
        ]);

        $results = $this->laracart->find(['key1' => 'matching']);

        $this->assertCount(2, $results);
        $this->assertEquals($item1->key1, $results[0]->key1);
        $this->assertEquals($item1->getHash(), $results[0]->getHash());
        $this->assertEquals($item2->key1, $results[1]->key1);
        $this->assertEquals($item2->getHash(), $results[1]->getHash());
    }

    /**
     * Test that an multiple matching items are found by the value of an option.
     */
    public function testFindingAnItemOnAnEmptyCartReturnsNoMatches()
    {
        $this->assertNull($this->laracart->find(['key1' => 'matching']));
    }

    /**
     * Test an item is returned when finding multiple criteria.
     */
    public function testFindingAnItemWithMultipleCriteria()
    {
        $item1 = $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $item2 = $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value3',
        ]);

        $result1 = $this->laracart->find(['key1' => 'value1', 'key2' => 'value2']);
        $result2 = $this->laracart->find(['key1' => 'value1', 'key2' => 'value3']);

        $this->assertEquals($item1->key2, $result1->key2);
        $this->assertEquals($item1->getHash(), $result1->getHash());

        $this->assertEquals($item2->key2, $result2->key2);
        $this->assertEquals($item2->getHash(), $result2->getHash());

        $this->assertNull($this->laracart->find(['key1' => 'value2', 'key2' => 'value2']));

        $result3 = $this->laracart->find(['key1' => 'value1']);
        $this->assertCount(2, $result3);
        $this->assertEquals($item1->getHash(), $result3[0]->getHash());
        $this->assertEquals($item2->getHash(), $result3[1]->getHash());
    }

    /**
     * Test an item is found searching by name.
     */
    public function testFindingAnItemByName()
    {
        $item1 = $this->laracart->add('item1234', 'My Item', 1, 2, [
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $item2 = $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value3',
        ]);

        $result = $this->laracart->find(['name' => 'My Item']);

        $this->assertEquals($item1->name, $result->name);
        $this->assertEquals($item1->getHash(), $result->getHash());

        $this->assertNull($this->laracart->find(['name' => 'My Item', 'key2' => 'nomatch']));
    }

    public function testQtyUpdate()
    {
        $item = $this->addItem();

        $this->assertEquals(1, $this->laracart->count(false));

        $this->laracart->updateItem($item->getHash(), 'qty', 0);

        $this->assertEquals(0, $this->laracart->count(false));
    }

    public function testfindItemById()
    {
        $item = $this->addItem();
        $item->id = 123;

        $this->assertEquals($item, $this->laracart->find([
            'id' => 123,
        ]));
    }

    public function testTaxationTotal()
    {
        $this->addItem(2, 8.33, 1, [
            'tax' => '.2',
        ]);

        $this->assertEquals(19.99, $this->laracart->total(false));

        $this->app['config']->set('laracart.tax_by_item', true);

        $this->assertEquals(20.00, $this->laracart->total(false));
    }

    public function testSeparateTaxationTotal()
    {
        $this->app['config']->set('laracart.tax_by_item', true);

        $this->addItem(1, 8.33, 1, [
            'tax' => '.2',
        ]);

        $this->addItem(1, 8.33, 1, [
            'tax'  => '.2',
            'some' => 'test',
            'name' => '12313',
        ]);

        $this->assertEquals(20.00, $this->laracart->total(false));
    }
}
