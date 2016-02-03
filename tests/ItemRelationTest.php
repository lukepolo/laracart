<?php

/**
 * Class LaraCartTest
 */
class ItemRelationTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    public function testItemRelation()
    {
        $item = $this->addItem();

        $this->assertEmpty($item->itemModel);

        $item->setModel(\LukePOLO\LaraCart\Tests\Models\TestItem::class);


        $this->assertEquals(\LukePOLO\LaraCart\Tests\Models\TestItem::class, $item->itemModel);

        $this->assertEquals('itemID', $item->getModel()->id);
    }
}
