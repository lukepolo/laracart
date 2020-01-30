<?php

namespace LukePOLO\LaraCart\Tests;

use LukePOLO\LaraCart\Cart;
use LukePOLO\LaraCart\LaraCart;

/**
 * Class LaraCartTestTrait.
 */
trait LaraCartTestTrait
{
    /** @var LaraCart */
    public $laracart;

    /**
     * Setup the test functions with laracart.
     */
    public function setUp(): void
    {
        parent::setUp();

        /* @var Cart laracart */
        $this->laracart = new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager);
    }

    /**
     * Default tax setup.
     *
     * @param $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->session = $app['session'];
        $this->events = $app['events'];
        $this->authManager = $app['auth'];

        $app['config']->set('database.default', 'testing');

        // Setup default database to use sqlite :memory:
        $app['config']->set('laracart.tax', '.07');
    }

    /**
     * Sets the package providers.
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['\LukePOLO\LaraCart\LaraCartServiceProvider'];
    }

    /**
     * Easy way to add an item for many tests.
     *
     * @param int   $qty
     * @param int   $price
     * @param bool  $taxable
     * @param array $options
     *
     * @return mixed
     */
    private function addItem($qty = 1, $price = 1, $taxable = true, $options = [])
    {
        if (empty($options)) {
            $options = [
                'b_test' => 'option_1',
                'a_test' => 'option_2',
            ];
        }

        return $this->laracart->add(
            'itemID',
            'Testing Item',
            $qty,
            $price,
            $options,
            $taxable
        );
    }
}
