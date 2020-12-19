<?php

namespace Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class PublishConcertTest
 * @package Feature\Backstage
 */
class PublishConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以發佈自己的音樂會()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('backstage/concerts');
        $concert = $concert->fresh();
        static::assertTrue($concert->isPublished());
        static::assertEquals(3, $concert->ticketsRemaining());
    }

    public function test_一場音樂會只能被發佈一次()
    {
        $user = User::factory()->create();
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(422);
        static::assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    public function test_管理者不能發佈別人的音樂會()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $otherUser->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);

        $concert = $concert->fresh();
        static::assertFalse($concert->isPublished());
        static::assertEquals(0, $concert->ticketsRemaining());
    }

    public function test_一般使用者不能發佈音樂會()
    {
        $concert = Concert::factory()->unpublished()->create([
            'ticket_quantity' => 3,
        ]);

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');

        $concert = $concert->fresh();
        static::assertFalse($concert->isPublished());
        static::assertEquals(0, $concert->ticketsRemaining());
    }

    public function test_不存在的音樂會不能被發佈()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 999,
        ]);

        $response->assertStatus(404);
    }
}
