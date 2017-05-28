<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到新增音樂會的表單新增頁()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    public function test_未登入的人不能看到新增音樂會的表單新增頁()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
