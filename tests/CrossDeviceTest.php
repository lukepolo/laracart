<?php

/**
 * Class CrossDeviceTest
 */
class CrossDeviceTest extends Orchestra\Testbench\TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;
    use \LukePOLO\LaraCart\Tests\LaraCartTestTrait;

    /**
     * Testing migrations
     */
    public function testMigrations()
    {
        $this->artisan('migrate', [
            '--realpath' => realpath(__DIR__ . '/../migrations'),
        ]);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

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

    /**
     * Testing to make sure the session gets saved to the model
     */
    public function testSaveCartSessionID()
    {
        $this->app['config']->set('laracart.cross_devices', true);
        $user = new \LukePOLO\LaraCart\Tests\Models\User();
        $this->authManager->login($user);

        $this->addItem();

        $this->assertEquals($this->session->getId(), $this->authManager->user()->cart_session_id);
    }
}
