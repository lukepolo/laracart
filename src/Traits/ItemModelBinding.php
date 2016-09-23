<?php

namespace LukePOLO\LaraCart\Traits;

use Illuminate\Database\Eloquent\Model;
use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class ItemModelBinding
 * @package LukePOLO\LaraCart\Traits
 */
trait ItemModelBinding
{
    public $itemModel;
    public $itemModelRelations;

    public function bindModelToItem(Model $itemModel)
    {
        $this->id = $itemModel->getKeyValue();
        $this->name = $itemModel->getName();
        $this->taxable = $itemModel->isTaxable();
        $this->lineItem = $itemModel->isLineItem();
        $this->price = floatval($itemModel->getPrice());
        $this->tax = $itemModel->getTax() ? $itemModel->getTax() : config('laracart.tax');
    }

    /**
     * Sets the related model to the item
     * @param $itemModel
     * @param array $relations
     * @throws ModelNotFound
     */
    public function setModel($itemModel, array $relations = [])
    {
        if (!class_exists($itemModel)) {
            throw new ModelNotFound('Could not find relation model');
        }

        $this->itemModel = $itemModel;
        $this->itemModelRelations = $relations;
    }

    /**
     * Returns a Model
     * @throws ModelNotFound
     */
    public function getModel()
    {
        $itemModel = (new $this->itemModel)->with($this->itemModelRelations)->find($this->id);

        if (empty($itemModel)) {
            throw new ModelNotFound('Could not find the item model for ' . $this->id);
        }

        return $itemModel;
    }
}
