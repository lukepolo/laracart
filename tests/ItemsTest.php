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
        $this->assertEquals(10, $item->price(false));
        $this->assertEquals(30, $item->subTotal(false));
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

        $this->assertEquals('.70', $item->tax());

        $this->assertEquals('4.05', $this->laracart->total(false));
    }
}
