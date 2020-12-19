<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Concert;
use App\Jobs\SendAttendeeMessage;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Class MessageAttendeesTest
 * @package Tests\Feature\Backstage
 */
class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到自己的音樂會訊息()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        static::assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者不能看到任何別的管理者的音樂會訊息()
    {
        $user = User::factory()->create();
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => User::factory()->create(),
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    public function test_消費者不能進入看到任何音樂會訊息()
    {
        $concert = \Database\Factories\ConcertFactory::createPublished();
        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertRedirect('/login');
    }

    public function test_管理者可以發送訊息給所有參加者()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Concert $concert */
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        static::assertEquals($concert->id, $message->concert_id);
        static::assertEquals('My subject', $message->subject);
        static::assertEquals('My message', $message->message);

        Queue::assertPushed(SendAttendeeMessage::class, function($job) use($message) {
            return $job->attendeeMessage->is($message);
        });
    }

    public function test_管理者不能發訊息到別人的音樂會()
    {
        Queue::fake();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertStatus(404);
        static::assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    public function test_一般使用者不能發訊息到任何一個音樂會()
    {
        Queue::fake();
        $concert = \Database\Factories\ConcertFactory::createPublished();

        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect('/login');
        static::assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    public function test_subject_為必填()
    {
        Queue::fake();
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Concert $concert */
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
                         ->actingAs($user)
                         ->post("/backstage/concerts/{$concert->id}/messages", [
                             'subject' => '',
                             'message' => 'My message',
                         ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('subject');
        static::assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    public function test_message_為必填()
    {
        Queue::fake();
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Concert $concert */
        $concert = \Database\Factories\ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
                         ->actingAs($user)
                         ->post("/backstage/concerts/{$concert->id}/messages", [
                             'subject' => 'My subject',
                             'message' => '',
                         ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('message');
        static::assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
