<?php

/**
 * Class ItemsTest
 */
class ItemsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test if we can add an item to the cart
     */
    public function testAddItem()
    {
        $this->addItem();
        $this->addItem();

        $this->assertEquals(1, $this->laracart->count(false));
        $this->assertEquals(2, $this->laracart->count());
    }

    /**
     * Tests when we add multiples of the same item it updates the qty properly
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
            'b' => 1
        ];

        $item = $this->addItem(1, 1, false, $options);
        $this->addItem(1, 1, false, array_reverse($options));

        $this->assertEquals(2, $item->qty);
    }

    /**
     * Test if we can add an line item to the cart
     */
    public function testAddLineItem()
    {
        $this->addItem();
        $this->addItem();

        $this->laracart->addLine(
            'itemID',
            'Testing Item',
            1,
            '1', [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(2, $this->laracart->count(false));
        $this->assertEquals(3, $this->laracart->count());
    }

    /**
     * Test getting an item from the cart
     */
    public function testGetItem()
    {
        $item = $this->addItem();
        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));
    }

    /**
     * Test updating the item
     */
    public function testUpdateItem()
    {
        $item = $this->addItem();

        $this->laracart->updateItem($item->getHash(), 'qty', 4);

        $this->assertEquals(4, $item->qty);
    }

    /**
     * Test getting all the items from the cart
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
     * Test the price and qty based on the item
     */
    public function testItemPriceAndQty()
    {
        $item = $this->addItem(3, 10);

        $this->assertEquals(3, $item->qty);
        $this->assertEquals(10, $item->getPrice(false, false));
        $this->assertEquals(30, $item->subTotal(false, false));
    }

    /**
     * Test removing an item from the cart
     */
    public function testRemoveItem()
    {
        $item = $this->addItem();

        $this->laracart->removeItem($item->getHash());

        $this->assertEmpty($this->laracart->getItem($item->getHash()));
    }

    /**
     * Test seeing a valid and invalid price
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
        } catch(\LukePOLO\LaraCart\Exceptions\InvalidPrice $e) {
            $this->assertEquals('The price must be a valid number', $e->getMessage());
        }
    }

    /**
     * Test seeing a valid and invalid qty
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
        } catch(\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }

        try {
            $item->qty = 'a';
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidQuantity::class);
        } catch(\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }

        try {
            $item->qty = -1;
            $this->setExpectedException(\LukePOLO\LaraCart\Exceptions\InvalidQuantity::class);
        } catch(\LukePOLO\LaraCart\Exceptions\InvalidQuantity $e) {
            $this->assertEquals('The quantity must be a valid number', $e->getMessage());
        }
    }

    /**
     * Tests the different taxes on items
     */
    public function testDifferentTaxes() {

        $item = $this->addItem();

        $prevHash = $item->getHash();

        $item->tax = .05;

        $this->assertNotEquals($prevHash, $item->getHash());

        $item = $this->addItem();
        $item->tax = .3;

        $this->assertEquals('2.35', $this->laracart->total(false));

        $item = $this->addItem(1, 1, true, [
            'tax' => .7
        ]);

        $this->assertEquals('1.70', $item->subTotal(true, false));
    }

    /*
     * Test that an item can be found by the value of an option
     */
    public function testFindingAnItemByOptionSucceeds() 
    {
        $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value1',
        ]);
        
        $this->addItem(1, 1, true, [
            'key1' => 'notmatching',
            'key2' => 'value2',
        ]);

        $this->assertCount(1, $this->laracart->find(['key1' => 'matching']));
        $this->assertCount(1, $this->laracart->find(['key2' => 'value2']));
    }

    /*
     * Test that an item is not found by the value of an option when it does not exist
     */
    public function testFindingAnItemByOptionFails() 
    {
        $this->addItem(1, 1, true, [
            'key1' => 'notmatching',
        ]);

        $this->addItem(1, 1, true, [
            'key2' => 'notmatching',
        ]);

        $this->assertCount(0, $this->laracart->find(['key1' => 'matching']));
    }

    /*
     * Test that multiple matching items are found by the value of an option 
     */
    public function testFindingAnItemReturnsMultipleMatches() 
    {
        $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value1',
        ]);

        $this->addItem(1, 1, true, [
            'key1' => 'matching',
            'key2' => 'value2',
        ]);

        $this->addItem(1, 1, true, [
            'key1' => 'nomatch',
        ]);

        $this->assertCount(2, $this->laracart->find(['key1' => 'matching']));
    }

    /*
     * Test that an multiple matching items are found by the value of an option 
     */
    public function testFindingAnItemOnAnEmptyCartReturnsNoMatches() 
    {
        $this->assertCount(0, $this->laracart->find(['key1' => 'matching']));
    }

    /*
     * Test an item is returned when finding multiple criteria 
     */
    public function testFindingAnItemWithMultipleCriteria() 
    {
        $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value3',
        ]);

        $this->assertCount(1, $this->laracart->find(['key1' => 'value1', 'key2' => 'value3']));
        $this->assertCount(1, $this->laracart->find(['key1' => 'value1', 'key2' => 'value2']));
        $this->assertCount(0, $this->laracart->find(['key1' => 'value2', 'key2' => 'value2']));
        $this->assertCount(2, $this->laracart->find(['key1' => 'value1']));
    }

    /*
     * Test an item is found searching by name
     */
    public function testFindingAnItemByName() 
    {
        $this->laracart->add('item1234', 'My Item', 1, 2, [
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->addItem(1, 1, true, [
            'key1' => 'value1',
            'key2' => 'value3',
        ]);

        $this->assertCount(1, $this->laracart->find(['name' => 'My Item']));
        $this->assertCount(0, $this->laracart->find(['name' => 'My Item', 'key2' => 'nomatch']));
    }

}
