<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class AcceptInvitationTest
 * @package Tests\Feature
 */
class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_看到一個尚未使用的邀請()
    {
        $this->withoutExceptionHandling();

        $invitation = Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        static::assertTrue($response->data('invitation')->is($invitation));
    }

    public function test_查看一個已使用的邀請碼會得到404()
    {
        Invitation::factory()->create([
            'user_id' => User::factory()->create(),
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    public function test_查看一個不存在的邀請碼會得到404()
    {
        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    public function test_使用合格的邀請碼註冊成功()
    {
        $this->withoutExceptionHandling();

        $invitation = Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/backstage/concerts');

        static::assertEquals(1, User::count());

        $user = User::first();
        static::assertAuthenticatedAs($user);
        static::assertEquals('john@example.com', $user->email);
        static::assertTrue(Hash::check('secret', $user->password));
        static::assertTrue($invitation->fresh()->user->is($user));
    }

    public function test_使用已註冊過的邀請碼會得到404()
    {
        Invitation::factory()->create([
            'user_id' => User::factory()->create(),
            'code'    => 'TESTCODE1234',
        ]);

        static::assertEquals(1, User::count());

        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertStatus(404);
        static::assertEquals(1, User::count());
    }

    public function test_使用不存在的邀請碼會得到404()
    {
        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertStatus(404);
        static::assertEquals(0, User::count());
    }

    public function test_email為必填()
    {
        Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => '',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        static::assertEquals(0, User::count());
    }

    public function test_email格式正確()
    {
        Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'not-an-email',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        static::assertEquals(0, User::count());
    }

    public function test_email必須為唯一值()
    {
        User::factory()->create(['email' => 'john@example.com']);
        static::assertEquals(1, User::count());

        Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        static::assertEquals(1, User::count());
    }

    public function test_password為必填()
    {
        Invitation::factory()->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'john@example.com',
            'password'        => '',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        static::assertEquals(0, User::count());
    }
}
