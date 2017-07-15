<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function($value) {
            Assert::AssertTrue($this->contains($value), "Failed asserting that the collection contained the specified value.");
        });

        Collection::macro('assertNotContains', function($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection did not contain the specified value.");
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
        $response->data('concerts')->assertContains($concertB);
        $response->data('concerts')->assertContains($concertD);
        $response->data('concerts')->assertNotContains($concertC);
    }
}
