<?php

namespace Tests\Unit\Jobs;

use ConcertFactory;
use OrderFactory;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
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
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        // 這三個人都應該要收到 message
        $orderA = OrderFactory::createForConcert($concert, ['email' => 'alex@example.com']);
        $orderB = OrderFactory::createForConcert($concert, ['email' => 'sam@example.com']);
        $orderC = OrderFactory::createForConcert($concert, ['email' => 'taylor@example.com']);

        // $job = new SendAttendeeMessage($message);
        // $job->handle();
        // 使用 dispatch() 相當於 handle()
        SendAttendeeMessage::dispatch($message);

        Mail::assertSent(AttendeeMessage::class, function($mail) {
            return $mail->hasTo('alex@example.com');
        });
        Mail::assertSent(AttendeeMessage::class, function($mail) {
            return $mail->hasTo('asam@example.com');
        });
        Mail::assertSent(AttendeeMessage::class, function($mail) {
            return $mail->hasTo('taylor@example.com');
        });
    }
}
