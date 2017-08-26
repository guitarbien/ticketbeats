<?php

namespace Tests\Feature\Backstage;

use App\User;
use ConcertFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到自己的音樂會訊息()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者不能看到任何別的管理者的音樂會訊息()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => factory(User::class)->create(),
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    public function test_消費者不能進入看到任何音樂會訊息()
    {
        $concert = ConcertFactory::createPublished();
        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertRedirect('/login');
    }
}
