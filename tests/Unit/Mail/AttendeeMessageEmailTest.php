<?php

namespace Tests\Unit\Mail;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

/**
 * Class AttendeeMessageEmailTest
 * @package Tests\Unit\Mail
 */
class AttendeeMessageEmailTest extends TestCase
{
    public function test_email要有正確的主旨和內容()
    {
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $email = new AttendeeMessageEmail($message);

        static::assertEquals("My subject", $email->build()->subject);
        static::assertEquals("My message", trim($this->render($email)));
    }

    private function render(Mailable $mailable)
    {
        $mailable->build();
        return view($mailable->textView, $mailable->buildViewData())->render();
    }
}
