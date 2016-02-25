<?php

/**
 * Class CrossDeviceTest
 */
class CrossDeviceTest extends Orchestra\Testbench\TestCase
{
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     *  Test getting the old session
     */
    public function testGetOldSession()
    {
        $newCart = new \LukePOLO\LaraCart\LaraCart($this->session, $this->events, $this->authManager);

        $this->addItem();
        $this->addItem();

        $this->app['config']->set('laracart.cross_devices', true);

        $user = new \LukePOLO\LaraCart\Tests\Models\User();

        $this->assertEquals(0, $newCart->count(false));
        $this->assertEquals(1, $this->count(false));

        $user->cart_session_id = $this->session->getId();
        $this->authManager->login($user);

        $newCart->get();

        $this->assertEquals($newCart->count(false), $this->count(false));
    }
}
