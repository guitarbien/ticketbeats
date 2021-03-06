<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Class PromoterLoginTest
 * @package Tests\Browser
 */
class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A basic browser test example.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function test_登入成功()
    {
        factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'jane@example.com')
                    ->type('password', 'super-secret-password')
                    ->press('Log in')
                    ->assertPathIs('/backstage/concerts');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function test_登入失敗()
    {
        factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'jane@example.com')
                    ->type('password', 'wrong-password')
                    ->press('Log in')
                    ->assertPathIs('/login')
                    ->assertInputValue('email', 'jane@example.com')
                    ->assertSee('credentials do not match');
        });
    }
}
