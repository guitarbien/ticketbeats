<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Class PromoterLoginTest
 * @package Tests\Feature\Backstage
 */
class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    public function test_以合法資訊登入()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'super-secret-password',
        ]);

        $response->assertRedirect('/backstage/concerts');
        static::assertTrue(Auth::check());
        static::assertTrue(Auth::user()->is($user));
    }

    public function test_以不合法資訊登入()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'not-the-right-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        static::assertTrue(session()->hasOldInput('email'));
        static::assertFalse(session()->hasOldInput('password'));
        static::assertFalse(Auth::check());
    }

    public function test_以不存在的帳號登入()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/login', [
            'email'    => 'xxxx@example.com',
            'password' => 'not-the-right-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        static::assertTrue(session()->hasOldInput('email'));
        static::assertFalse(session()->hasOldInput('password'));
        static::assertFalse(Auth::check());
    }

    public function test_登出目前的使用者()
    {
        Auth::login(User::factory()->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        static::assertFalse(Auth::check());
    }
}
