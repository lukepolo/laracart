<?php

class ItemsTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testAddItem()
    {
        $this->laracart->add(
            'itemID',
            'Testing Item',
            1,
            '1', [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->laracart->add(
            'itemID',
            'Testing Item',
            1,
            '1', [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

        $this->assertEquals(1, $this->laracart->count(false));
    }

    public function testAddLineItem()
    {
        $this->laracart->addLine(
            'itemID',
            'Testing Item',
            1,
            '1', [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ]
        );

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
    }

    public function testGetItem()
    {
        $item = $this->laracart->add(
            'itemID',
            'Testing Item',
            1,
            '1'
        );

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));
    }

    public function testGetItems()
    {
        $this->laracart->add(
            '1',
            'Testing Item',
            1,
            '1'
        );

        $this->laracart->add(
            '2',
            'Testing Item',
            2,
            '2'
        );

        $items = $this->laracart->getItems();

        $this->assertInternalType('array', $items);

        $this->assertCount(2, $items);

        $this->containsOnlyInstancesOf(LukePOLO\LaraCart\CartItem::class, $items);
    }

    public function testItemPriceAndQty()
    {
        $item = $this->laracart->add(
            'itemID',
            'Testing Item',
            3,
            '10'
        );

        $this->assertEquals(3, $item->qty);
        $this->assertEquals(10, $item->price);
    }

    public function testRemoveItem()
    {
        $item = $this->laracart->add(
            'itemID',
            'Testing Item',
            1,
            '1'
        );

        $this->laracart->removeItem($item->getHash());

        $this->assertEmpty($this->laracart->getItem($item->getHash()));
    }
}
