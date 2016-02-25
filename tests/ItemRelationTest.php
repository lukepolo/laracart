<?php

use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class LaraCartTest
 */
class ItemRelationTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Tests the item relations
     */
    public function testItemRelation()
    {
        $item = $this->addItem();

        $this->assertEmpty($item->itemModel);

        $item->setModel(\LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->assertEquals(\LukePOLO\LaraCart\Tests\Models\TestItem::class, $item->itemModel);

        $this->assertEquals('itemID', $item->getModel()->id);
    }

    public function testItemRelationModelException()
    {
        $item = $this->addItem();

        try {
            $item->setModel('asdfasdf');
            $this->setExpectedException(ModelNotFound::class);
        } catch (ModelNotFound $e) {
            $this->assertEquals('Could not find relation model', $e->getMessage());
        }
    }
}
