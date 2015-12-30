<?php

namespace LukePOLO\LaraCart\Tests;

trait LaraCartTestTrait
{
    public function setUp()
    {
        parent::setUp();
        $this->laracart = new \LukePOLO\LaraCart\LaraCart();
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('laracart.tax', '.07');
    }

    protected function getPackageProviders($app)
    {
        return ['\LukePOLO\LaraCart\LaraCartServiceProvider'];
    }

    private function addItem($qty = 1, $price = 1, $taxable = true)
    {
        return $this->laracart->add(
            'itemID',
            'Testing Item',
            $qty,
            $price, [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ],
            $taxable
        );
    }
}