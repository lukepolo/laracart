<?php

namespace LukePOLO\LaraCart\Tests\Models;

/**
 * Class TestItem
 *
 * @package LukePOLO\LaraCart\Tests\Models
 */
class TestItem
{

    public $id = 'itemID';
    public $name = 'Test Item';

    /**
     * Finds the id of this model
     *
     * @param $id
     *
     * @return $this
     */
    public function findOrFail($id)
    {
        return $this;
    }
}