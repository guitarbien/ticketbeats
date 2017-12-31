<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    public function test_看到一個尚未使用的邀請()
    {
        $invitation = factory(Invitation::class)->create([
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }
}
