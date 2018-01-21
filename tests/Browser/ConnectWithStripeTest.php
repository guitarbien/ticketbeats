<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Class ConnectWithStripeTest
 * @package Tests\Browser
 */
class ConnectWithStripeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function test_可以成功連結stripe_account()
    {
        $user = factory(User::class)->create([
            'stripe_account_id'   => null,
            'stripe_access_token' => null,
        ]);

        $this->browse(function (Browser $browser) use($user) {
            $browser->loginAs($user)
                    ->visit('/backstage/stripe-connect/authorize')
                    ->assertUrlIs('https://connect.stripe.com/oauth/authorize')
                    ->assertQueryStringHas('response_type', 'code')
                    ->assertQueryStringHas('scope', 'read_write')
                    ->assertQueryStringHas('client_id', config('services.stripe.client_id'));
        });
    }
}
