<?php

namespace App\Mail;

use App\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class AttendeeMessageEmail
 * @package App\Mail
 */
class AttendeeMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public AttendeeMessage $attendeeMessage)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->attendeeMessage->subject)
                    ->text('emails.attendee-message-email');
    }
}
