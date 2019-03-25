<?php

use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class LaraCartTest.
 */
class ItemRelationTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Tests the item relations.
     */
    public function testItemRelation()
    {
        $item = $this->addItem();

        $this->assertEmpty($item->itemModel);

        $item->setModel(\LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->assertEquals(\LukePOLO\LaraCart\Tests\Models\TestItem::class, $item->getItemModel());

        $this->assertEquals('itemID', $item->getModel()->id);

        try {
            $item->id = 'fail';
            $item->getModel();
            $this->setExpectedException(ModelNotFound::class);
        } catch (ModelNotFound $e) {
            $this->assertEquals('Could not find the item model for fail', $e->getMessage());
        }
    }

    /**
     * Test for exception if could not find model.
     */
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

    /**
     * Testing adding via item id.
     */
    public function testAddItemID()
    {
        $this->laracart->itemModel = \LukePOLO\LaraCart\Tests\Models\TestItem::class;
        $this->laracart->item_model_bindings = [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ];

        $this->app['config']->set('laracart.item_model', \LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->app['config']->set('laracart.item_model_bindings', [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ]);

        $item = $this->laracart->add('123123');

        $this->assertEquals($item->getModel()->id, $item->model->id);

        try {
            $this->laracart->add('fail');
        } catch (ModelNotFound $e) {
            $this->assertEquals('Could not find the item fail', $e->getMessage());
        }

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));

        $this->assertEquals($item->id, 'itemID');
        $this->assertEquals($item->name, 'Test Item');
        $this->assertEquals($item->qty, 1);
        $this->assertEquals($item->tax, '.07');
        $this->assertEquals($item->price, 5000.01);
        $this->assertEquals($item->taxable, false);
    }

    /**
     * Testing adding a item model.
     */
    public function testAddItemModel()
    {
        $this->app['config']->set('laracart.item_model', \LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->app['config']->set('laracart.item_model_bindings', [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ]);

        $item = new \LukePOLO\LaraCart\Tests\Models\TestItem([
            'price'   => 5000.01, // absurd!
            'taxable' => false,
            'tax'     => '.5',
        ]);

        $item = $this->laracart->add($item);

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));

        $this->assertEquals($item->id, 'itemID');
        $this->assertEquals($item->name, 'Test Item');
        $this->assertEquals($item->qty, 1);
        $this->assertEquals($item->tax, '.5');
        $this->assertEquals($item->price, 5000.01);
        $this->assertEquals($item->taxable, false);
    }

    /**
     * gtesting multiple item models at once.
     */
    public function testAddMultipleItemModel()
    {
        $this->app['config']->set('laracart.item_model', \LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->app['config']->set('laracart.item_model_bindings', [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ]);

        $item = new \LukePOLO\LaraCart\Tests\Models\TestItem([
            'price'   => 5000.01, // absurd!
            'taxable' => false,
            'tax'     => '.5',
        ]);

        $this->laracart->add($item);
        $item = $this->laracart->add($item);

        $this->assertEquals(2, $this->laracart->getItem($item->getHash())->qty);
    }

    /**
     * Testing adding a model to a line item.
     */
    public function testAddItemModelLine()
    {
        $this->app['config']->set('laracart.item_model', \LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->app['config']->set('laracart.item_model_bindings', [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ]);

        $item = new \LukePOLO\LaraCart\Tests\Models\TestItem([
            'price'   => 5000.01, // absurd!
            'taxable' => false,
            'tax'     => '.5',
        ]);

        $item = $this->laracart->addLine($item);

        $this->assertEquals($item, $this->laracart->getItem($item->getHash()));

        $this->assertEquals($item->id, 'itemID');
        $this->assertEquals($item->name, 'Test Item');
        $this->assertEquals($item->qty, 1);
        $this->assertEquals($item->tax, '.5');
        $this->assertEquals($item->price, 5000.01);
        $this->assertEquals($item->taxable, false);
    }

    /**
     * Testing adding multiple item models per row.
     */
    public function testAddMultipleItemModelLine()
    {
        $this->app['config']->set('laracart.item_model', \LukePOLO\LaraCart\Tests\Models\TestItem::class);

        $this->app['config']->set('laracart.item_model_bindings', [
            \LukePOLO\LaraCart\CartItem::ITEM_ID      => 'id',
            \LukePOLO\LaraCart\CartItem::ITEM_NAME    => 'name',
            \LukePOLO\LaraCart\CartItem::ITEM_PRICE   => 'price',
            \LukePOLO\LaraCart\CartItem::ITEM_TAXABLE => 'taxable',
            \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
                'tax',
            ],
        ]);

        $item = new \LukePOLO\LaraCart\Tests\Models\TestItem([
            'price'   => 5000.01, // absurd!
            'taxable' => false,
            'tax'     => '.5',
        ]);

        $this->laracart->addLine($item);
        $item = $this->laracart->addLine($item);

        $this->assertEquals(1, $this->laracart->getItem($item->getHash())->qty);
    }
}
