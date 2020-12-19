<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use App\Mail\InvitationEmail;
use Tests\TestCase;

/**
 * Class InvitationEmailTest
 * @package Tests\Unit\Mail
 */
class InvitationEmailTest extends TestCase
{
    public function test_email要有接受邀請的連結()
    {
        // 使用 create() 會真的操作 db, make() 則是 memory
        $invitation = Invitation::factory()->make([
            'email' => 'john@example.com',
            'code'  => 'TESTCODE1234',
        ]);

        $email = new InvitationEmail($invitation);

        static::assertStringContainsString(url('/invitations/TESTCODE1234'), $email->render());
    }

    public function test_email主旨正確()
    {
        $invitation = Invitation::factory()->make();

        $email = new InvitationEmail($invitation);

        static::assertEquals("You're invited to join TicketBeast!", $email->build()->subject);
    }
}
