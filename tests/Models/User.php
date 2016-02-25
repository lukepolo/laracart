<?php

namespace LukePOLO\LaraCart\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class TestItem
 *
 * @package LukePOLO\LaraCart\Tests\Models
 */
class User extends Authenticatable
{
    public $id = '1';
    public $cart_sessoin_id;

    public function save(array $options = [])
    {
        $this->cart_sessoin_id = $this->getAttribute('cart_sessoin_id');
    }
}