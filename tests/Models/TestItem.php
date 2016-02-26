<?php

namespace LukePOLO\LaraCart\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Class TestItem
 *
 * @package LukePOLO\LaraCart\Tests\Models
 */
class TestItem extends Model
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
        return Mockery::mock(new static);
    }

    /**
     * Begin querying a model with eager loading.
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        return Mockery::mock(new static);
    }
}