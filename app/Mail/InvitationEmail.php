<?php

namespace App\Mail;

use App\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class InvitationEmail
 * @package App\Mail
 */
class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * InvitationEmail constructor.
     * @param Invitation $invitation
     */
    public function __construct(public Invitation $invitation)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invitation-email')
                    ->subject("You're invited to join TicketBeast!");
    }
}
