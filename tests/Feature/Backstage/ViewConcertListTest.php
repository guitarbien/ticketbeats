<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        TestResponse::macro('data', function($key) {
            return $this->original->getData()[$key];
        });
    }

    public function test_一般使用者不能看到管理者的音樂列表頁()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_管理者只可以看到自己的音樂會的列表()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertB = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertC = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concertD = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);
        $response->data('concerts')->assertContains($concertA);
        $this->assertTrue($response->data('concerts')->contains($concertA));
        $this->assertTrue($response->data('concerts')->contains($concertB));
        $this->assertTrue($response->data('concerts')->contains($concertD));
        $this->assertFalse($response->data('concerts')->contains($concertC));
    }
}
