<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
