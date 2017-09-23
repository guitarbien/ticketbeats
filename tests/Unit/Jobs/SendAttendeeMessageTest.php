<?php

namespace Tests\Unit\Jobs;

use ConcertFactory;
use Illuminate\Mail\Mailable;
use OrderFactory;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SendAttendeeMessageTest extends TestCase
{
    use DatabaseMigrations;

    public function test_可以發送訊息給全部音樂會的參加者()
    {
        Mail::fake();

        $concert = ConcertFactory::createPublished();
        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject'    => 'My subject',
            'message'    => 'My message',
        ]);

        // 這三個購買人都應該要收到 message
        $orderA = OrderFactory::createForConcert($concert, ['email' => 'alex@example.com']);
        $orderB = OrderFactory::createForConcert($concert, ['email' => 'sam@example.com']);
        $orderC = OrderFactory::createForConcert($concert, ['email' => 'taylor@example.com']);

        // 這個購買人不應該要收到 message
        $otherConcert = ConcertFactory::createPublished();
        OrderFactory::createForConcert($otherConcert, ['email' => 'jane@example.com']);

        // $job = new SendAttendeeMessage($message);
        // $job->handle();
        // 使用 dispatch() 相當於 handle()
        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageEmail::class, function(AttendeeMessageEmail $mail) use ($message) {
            return $mail->hasTo('alex@example.com') && $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function(AttendeeMessageEmail $mail) use ($message) {
            return $mail->hasTo('sam@example.com') && $mail->attendeeMessage->is($message);
        });
        Mail::assertQueued(AttendeeMessageEmail::class, function(AttendeeMessageEmail $mail) use ($message) {
            return $mail->hasTo('taylor@example.com') && $mail->attendeeMessage->is($message);
        });
        Mail::assertNotQueued(AttendeeMessageEmail::class, function(AttendeeMessageEmail $mail) {
            return $mail->hasTo('jane@example.com');
        });
    }
}
