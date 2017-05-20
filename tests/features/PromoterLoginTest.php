<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Auth;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    public function test_以合法資訊登入()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'super-secret-password',
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    public function test_以不合法資訊登入()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'not-the-right-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }
}