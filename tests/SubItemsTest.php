<?php

/**
 * Class modifiersTotalTest
 */
class modifiersTotalTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test adding a sub item on a item
     */
    public function testaddModifier()
    {
        $item = $this->addItem();

        $subItem = $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50
        ]);

//        $this->assertInternalType('array', $item->modifiersTotal);

//        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItemModifierm::class, $item->modifiersTotal);

//        $this->assertEquals($subItem, $item->findModifier($subItem->hash()));
    }

    /**
     * Test getting the total from a sub item
     */
    public function testSubItemTotal()
    {
        $item = $this->addItem();

        $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50
        ]);

        $this->assertEquals('$2.50', $item->modifiersTotalTotal());
        $this->assertEquals('2.50', $item->modifiersTotalTotal()->amount());
    }

    /**
     * Test the sub items with more sub items
     */
    public function testSubItemItemsTotal()
    {
        $item = $this->addItem(1, 11);

        $item->addModifier([
            'price' => 2,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 1)
            ]
        ]);

        $this->assertEquals(3, $item->modifiersTotalTotal()->amount());

        $this->assertEquals(14, $item->subTotal()->amount());
        $this->assertEquals(14, $item->price()->amount());
    }

    /**
     * Testing totals for sub sub items
     */
    public function testmodifiersTotalmodifiersTotalTotal()
    {
        $item = $this->addItem(1, 11);

        $subItem = new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 2);

        $subItem->addModifier([
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'sub item item', 1, 1)
            ]
        ]);

        $item->addModifier([
            'items' => [
                $subItem
            ]
        ]);

        $this->assertEquals(3, $item->modifiersTotalTotal()->amount());

        $this->assertEquals(14, $item->subTotal()->amount());
        $this->assertEquals(14, $item->price()->amount());
    }

    /**
     * Test adding an item on a sub item
     */
    public function testaddModifierItems()
    {
        $item = $this->addItem();

        $subItem = $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10)
            ]
        ]);

        $this->assertInternalType('array', $subItem->items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $subItem->items);


        $this->assertEquals('$12.50', $subItem->price());
    }

    /**
     * Test adding an item on a sub item
     */
    public function testaddModifierItemsWithQty()
    {
        $item = $this->addItem();

        $subItem = $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10)
            ]
        ]);

        $this->assertInternalType('array', $subItem->items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $subItem->items);


        $this->assertEquals('$12.50', $subItem->price());

        $this->assertEquals('13.50', $this->laracart->subTotal()->amount());

        $item->qty = 2;

        $this->assertEquals('27.00', $this->laracart->subTotal()->amount());
    }

    /**
     * Test removing sub items
     */
    public function testRemoveSubItem()
    {
        $item = $this->addItem();

        $subItem = $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50
        ]);

        $subItemHash = $subItem->hash();

        $this->assertEquals($subItem, $item->findSubItem($subItemHash));

        $item->removeSubItem($subItemHash);

        $this->assertEquals(null, $item->findSubItem($subItemHash));

    }

    /**
     * Test to make sure taxable flag is working for total tax
     */
    public function testaddModifierItemsmodifiersTotalTax()
    {
        $item = $this->addItem();

        $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], false)
            ]
        ]);

        $this->assertEquals(13.50, $item->price()->amount());

        $this->assertEquals("0.25", $this->laracart->taxTotal()->amount());
    }

    /**
     * Test Tax in case the item is not taxed but modifiersTotal are taxable
     */
    public function testAddTaxedmodifiersTotalItemUnTaxed()
    {
        $item = $this->addItem(2, 2, false);

        // 12.50 * 2
        $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50,
            'taxable' => true,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], true)
            ]
        ]);

        $this->assertEquals(14.50, $item->price(false));

        $this->assertEquals(round(25 * .07, 2), $this->laracart->taxTotal(false));
    }

    /**
     * Test Tax in case the sub sub item is untaxed but sub item is taxed
     */
    public function testAddTaxedSubSubItemUntaxedSubItemTaxed()
    {
        $item = $this->addItem(1, 3, true);

        $subItem = new \LukePOLO\LaraCart\CartItem('itemId', 'test sub item', 1, 10, [], true);

        $subItem->addModifier([
            'items' => [
                // not taxable
                new \LukePOLO\LaraCart\CartItem('itemId', 'test sub sub item', 1, 10, [], false)
            ]
        ]);

        $item->addModifier([
            'items' => [
                $subItem
            ]
        ]);

        $this->assertEquals(23.00, $item->price(false));

        $this->assertEquals("0.91", $this->laracart->taxTotal(false));
    }

    public function testSearchmodifiersTotal()
    {
        $item = $this->addItem(2, 2, false);

        $subItem = $item->addModifier([
            'size' => 'XXL',
            'price' => 2.50,
            'taxable' => true,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [
                    'amItem' => true
                ], true)
            ]
        ]);

        $this->assertCount(0, $item->searchForSubItem(['size' => 'XL']));

        $itemsFound = $item->searchForSubItem(['size' => 'XXL']);

        $this->assertCount(1, $itemsFound);

        $itemFound =  $itemsFound[0];

        $this->assertEquals($subItem->hash() , $itemFound->hash());
        $this->assertEquals($subItem->size , $itemFound->size);
    }
}
