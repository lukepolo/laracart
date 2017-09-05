<?php

/**
 * Class modifiersTotalTest.
 */
class ModifiersTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Test adding a modifier on a item.
     */
    public function testAddModifier()
    {
        $item = $this->addItem();

        $modifier = $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $this->assertInternalType('array', $item->modifiers);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItemModifier::class, $item->modifiers);

        $this->assertEquals($modifier, $item->findModifier($modifier->hash()));
    }

    /**
     * Test getting the total from a modifier.
     */
    public function testModifierItemTotal()
    {
        $item = $this->addItem();

        $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $this->assertEquals('$2.50', $item->modifiersTotal());
        $this->assertEquals('2.50', $item->modifiersTotal()->amount());
    }

    /**
     * Test the modifiers with more modifiers.
     */
    public function testModifierItemsTotal()
    {
        $item = $this->addItem(1, 11);

        $item->addModifier([
            'price' => 2,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'modifier item', 1, 1),
            ],
        ]);

        $this->assertEquals(3, $item->modifiersTotal()->amount());

        $this->assertEquals(14, $item->subTotal()->amount());
        $this->assertEquals(14, $item->price()->amount());
    }

    /**
     * Testing totals for sub modifiers.
     */
    public function testModifiersTotalModifiersTotalTotal()
    {
        $item = $this->addItem(1, 11);

        $modifier = new \LukePOLO\LaraCart\CartItem('10', 'modifier item', 1, 2);

        $modifier->addModifier([
            'items' => [
                new \LukePOLO\LaraCart\CartItem('10', 'modifier item', 1, 1),
            ],
        ]);

        $item->addModifier([
            'items' => [
                $modifier,
            ],
        ]);

        $this->assertEquals(3, $item->modifiersTotal()->amount());

        $this->assertEquals(14, $item->subTotal()->amount());
        $this->assertEquals(14, $item->price()->amount());
    }

    /**
     * Test adding an item on a modifier.
     */
    public function testaddModifierItems()
    {
        $item = $this->addItem();

        $modifier = $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10),
            ],
        ]);

        $this->assertInternalType('array', $modifier->items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $modifier->items);

        $this->assertEquals('$12.50', $modifier->price());
    }

    /**
     * Test adding an item on a modifier.
     */
    public function testaddModifierItemsWithQty()
    {
        $item = $this->addItem();

        $modifier = $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10),
            ],
        ]);

        $this->assertInternalType('array', $modifier->items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $modifier->items);

        $this->assertEquals('$12.50', $modifier->price());

        $this->assertEquals('13.50', $this->laracart->subTotal()->amount());

        $item->qty = 2;

        $this->assertEquals('27.00', $this->laracart->subTotal()->amount());
    }

    /**
     * Test removing modifiers.
     */
    public function testRemoveModifier()
    {
        $item = $this->addItem();

        $modifier = $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
        ]);

        $modifierHash = $modifier->hash();

        $this->assertEquals($modifier, $item->findModifier($modifierHash));
        $item->removeModifier($modifierHash);

        $this->assertEquals(null, $item->findModifier($modifierHash));
    }

    /**
     * Test to make sure taxable flag is working for total tax.
     */
    public function testAddModifierItemsModifiersTotalTax()
    {
        $item = $this->addItem();

        $item->addModifier([
            'size'  => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], false),
            ],
        ]);

        $this->assertEquals(13.50, $item->price()->amount());

        $this->assertEquals('0.25', $this->laracart->taxTotal()->amount());
    }

    /**
     * Test Tax in case the item is not taxed but modifiersTotal are taxable.
     */
    public function testAddTaxedModifiersTotalItemUnTaxed()
    {
        $item = $this->addItem(2, 2, [
            'taxable' => false,
        ]);

        $item->addModifier([
            'size'    => 'XXL',
            'price'   => 2.50,
            'taxable' => true,
            'items'   => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [], true),
            ],
        ]);

        $this->assertEquals(14.50, $item->price(false)->amount());

        $this->assertEquals(29, $this->laracart->subtotal()->amount());

        // minus 4 cause we are not suppose to tax those items
        $this->assertEquals(round(25 * .07, 2), $this->laracart->taxTotal(false)->amount());
    }

    /**
     * Test Tax in case the sub modifier is untaxed but modifier is taxed.
     */
    public function testAddTaxedSubModifierUntaxedModifierTaxed()
    {
        $item = $this->addItem(1, 3);

        $modifier = new \LukePOLO\LaraCart\CartItem('itemId', 'test modifier', 1, 10, [], true);

        $modifier->addModifier([
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test sub modifier non taxable', 1, 10, [], false),
            ],
        ]);

        $item->addModifier([
            'items' => [
                $modifier,
            ],
        ]);

        $this->assertEquals(23.00, $item->price(false)->amount());

        $this->assertEquals('0.91', $this->laracart->taxTotal()->amount());
    }

    public function testSearchModifiersTotal()
    {
        $item = $this->addItem(2, 2, false);

        $modifier = $item->addModifier([
            'size'    => 'XXL',
            'price'   => 2.50,
            'taxable' => true,
            'items'   => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10, [
                    'amItem' => true,
                ], true),
            ],
        ]);

        $this->assertCount(0, $item->search(['size' => 'XL']));

        $itemsFound = $item->search(['size' => 'XXL']);

        $this->assertCount(1, $itemsFound);

        $itemFound = $itemsFound[0];

        $this->assertEquals($modifier->hash(), $itemFound->hash());
        $this->assertEquals($modifier->size, $itemFound->size);
    }
}
