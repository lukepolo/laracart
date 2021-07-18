<?php

/**
 * Class SubItemsTest.
 */
class SubItemsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test adding a sub item on a item.
     */
    public function testAddSubItem()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartSubItem::class, $item->subItems);

        $this->assertEquals($subItem, $item->findSubItem($subItem->getHash()));
    }

    /**
     * Test getting the total from a sub item.
     */
    public function testSubItemTotal()
    {
        $item = $this->addItem();

        $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $this->assertEquals(3.50, $item->subTotal(false));
        $this->assertEquals(2.50, $item->subItemsTotal(false));
    }

    /**
     * Test the sub items with more sub items.
     */
    public function testSubItemItemsTotal()
    {
        $item = $this->addItem(1, 10, true, [
            'tax' => .01,
        ]);

        $item->addSubItem([
            'price' => 10,
            'tax'   => .01,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 10, [
                    'tax' => .01,
                ]),
            ],
        ]);

        $this->assertEquals(20, $item->subItemsTotal(false));
        $this->assertEquals(30, $item->subTotal(false));
        $this->assertEquals(.30, $this->laracart->taxTotal(false));
        $this->assertEquals(30.30, $this->laracart->total(false));
    }

    public function testSubItemMultiQtyTaxation()
    {
        $item = $this->addItem(1, 10, true, [
            'tax' => .01,
        ]);

        $item->addSubItem([
            'price' => 10,
            'tax'   => .01,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 10, 1, [
                    'tax' => .01,
                ]),
            ],
        ]);

        $this->assertEquals(20, $item->subItemsTotal(false));
        $this->assertEquals(30, $item->subTotal(false));
        $this->assertEquals(.30, $this->laracart->taxTotal(false));
        $this->assertEquals(30.30, $this->laracart->total(false));
    }

    /**
     * Testing totals for sub sub items.
     */
    public function testSubItemsSubItemsTotal()
    {
        $item = $this->addItem(1, 11);

        $subItem = new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 2);

        $subItem->addSubItem([
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 1),
            ],
        ]);

        $item->addSubItem([
            'items' => [
                $subItem,
            ],
        ]);

        $this->assertEquals(3, $item->subItemsTotal(false));
        $this->assertEquals(14, $item->subTotal(false));
        $this->assertEquals(14.98, $item->total(false));
    }

    /**
     * Test adding an item on a sub item.
     */
    public function testAddSubItemItems()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10),
            ],
        ]);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $subItem->items);

        $this->assertEquals(12.50, $subItem->subTotal());
    }

    /**
     * Test adding an item on a sub item.
     */
    public function testAddSubItemItemsWithQty()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10),
            ],
        ]);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $subItem->items);

        $this->assertEquals(12.50, $subItem->subTotal());
        $this->assertEquals('13.50', $item->subTotal(false));

        $this->assertEquals('13.50', $this->laracart->subTotal(false));

        $item->qty = 2;
        $this->assertEquals('27.00', $item->subTotal(false));
        $this->assertEquals('27.00', $this->laracart->subTotal(false));
    }

    /**
     * Test removing sub items.
     */
    public function testRemoveSubItem()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $subItemHash = $subItem->getHash();

        $this->assertEquals($subItem, $item->findSubItem($subItemHash));

        $item->removeSubItem($subItemHash);

        $this->assertEquals(null, $item->findSubItem($subItemHash));
    }

    /**
     * Test to make sure taxable flag is working for total tax.
     */
    public function testAddSubItemItemsSubItemsTax()
    {
        $item = $this->addItem();

        $item->addSubItem([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], false),
            ],
        ]);

        $this->assertEquals(13.50, $item->subTotal(false));

        $this->assertEquals('0.25', $this->laracart->taxTotal(false));
    }

    /**
     * Test Tax in case the item is not taxed but subItems are taxable.
     */
    public function testAddTaxedSubItemsItemUnTaxed()
    {
        $item = $this->addItem(1, 1, false);

        // 12.50
        $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 2.50,
            'taxable' => true,
            'items'   => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], true),
            ],
        ]);

        $this->assertEquals(13.50, $item->subTotal(false));
        $this->assertEquals(.88, $this->laracart->taxTotal(false));
    }

    /**
     * Test Tax in case the sub sub item is untaxed but sub item is taxed.
     */
    public function testAddTaxedSubSubItemUntaxedSubItemTaxed()
    {
        $item = $this->addItem(1, 1, true);

        $subItem = new \LukePOLO\LaraCart\CartItem('itemId', 'test sub item', 1, 10, [], true);

        $subItem->addSubItem([
            'items' => [
                // not taxable
                new \LukePOLO\LaraCart\CartItem('itemId', 'test sub sub item', 1, 10, [], false),
            ],
        ]);

        $item->addSubItem([
            'items' => [
                $subItem,
            ],
        ]);

        $this->assertEquals(21.00, $item->subTotal(false));
        $this->assertEquals(0.77, $this->laracart->taxTotal(false));
    }

    public function testSearchSubItems()
    {
        $item = $this->addItem(2, 2, false);

        $subItem = $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 2.50,
            'taxable' => true,
            'items'   => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [
                    'amItem' => true,
                ], true),
            ],
        ]);

        $this->assertCount(0, $item->searchForSubItem(['size' => 'XL']));

        $itemsFound = $item->searchForSubItem(['size' => 'XXL']);

        $this->assertCount(1, $itemsFound);

        $itemFound = $itemsFound[0];

        $this->assertEquals($subItem->getHash(), $itemFound->getHash());
        $this->assertEquals($subItem->size, $itemFound->size);
    }

    public function testDefaultTaxOnSubItem()
    {
        $item = $this->addItem(1, 0);

        $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 10.00,
        ]);

        $this->assertEquals(0.7, $this->laracart->taxTotal(false));
    }

    public function testDifferentTaxtionsOnSubItems()
    {
        $item = $this->addItem(1, 10, true, [
            'tax' => .01,
        ]);

        $item->addSubItem([
            'size'    => 'XXL',
            'price'   => 10.00,
            'taxable' => true,
            'tax'     => .02,
        ]);

        $this->assertEquals(0.30, $this->laracart->taxTotal(false));
    }

    // TODO
//    public function testTaxSumary()
//    {
//        $item = $this->addItem(1, 10, true, [
//            'tax' => .01,
//        ]);
//
//        $item->addSubItem([
//            'size'    => 'XXL',
//            'price'   => 10.00,
//            'taxable' => true,
//            'tax'     => .02,
//        ]);
//
//        $this->assertEquals([
//            '0.01' => .10,
//            '0.02' => .20,
//        ], $item->taxSummary());
//    }
}
