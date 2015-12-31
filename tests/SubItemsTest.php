<?php

class SubItemsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testAddSubItem()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size' => 'XXL',
            'price' => 2.50
        ]);


        $this->assertEquals($subItem, $item->findSubItem($subItem->getHash()));
    }

    public function  testSubItemTotal()
    {
        $item = $this->addItem();

        $subItem = $item->addSubItem([
            'size' => 'XXL',
            'price' => 2.50
        ]);

        $this->assertEquals('$2.50', $item->subItemsTotal());
        $this->assertEquals('$2.68', $item->subItemsTotal(true));
        $this->assertEquals('2.50', $item->subItemsTotal(false, false));
        $this->assertEquals('2.68', $item->subItemsTotal(true, false));
    }

    public function testAddSubItemItems()
    {
        $item = $this->addItem();

        $item->addSubItem([
            'size' => 'XXL',
            'price' => 2.50,
            'items' => [
                new \LukePOLO\LaraCart\CartItem('itemId', 'test item', 1, 10)
            ]
        ]);

    }

    public function testRemoveSubItem()
    {

    }
}
