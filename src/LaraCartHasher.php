<?php

namespace LukePOLO\LaraCart;

class LaraCartHasher
{
    public function hash($data)
    {
        return md5(json_encode($data));
    }
}
