<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ForceStripeAccountTest
 * @package Tests\Unit\Http\Middleware
 */
class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_使用者必須和stripe_account綁定()
    {
        // 同 ->actingAs($user)，模擬某個使用者的操作
        $this->be(factory(User::class)->create([
            'stripe_account_id' => null,
        ]));

        $middleware  = new ForceStripeAccount;
        $response = $middleware->handle(new Request,  function($request) {
            // 若是走到第二個參數 callback，則代表已經出錯了
            $this->fail('Next middleware was called when it should not have been.');
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }

    public function test_使用者若已經綁定stripe_account則可以繼續操作()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'test_stripe_account_1234',
        ]));

        $request = new Request;

        $next = new class {
            public $called = false;

            /**
             * @param $request
             * @return mixed
             */
            public function __invoke($request)
            {
                $this->called = true;
                return $request;
            }
        };

        $middleware  = new ForceStripeAccount;
        $response = $middleware->handle($request, $next);

        // middleware 的第二個參數 $next 必須要被執行
        $this->assertTrue($next->called);

        // 參數也有被正確傳入到 $next 中
        $this->assertSame($response, $request);
    }

    public function test_所有後台的routes都該走過此middleware()
    {
        $routes = [
            'backstage.concerts.index',
            'backstage.concerts.new',
            'backstage.concerts.store',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store',
        ];

        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
