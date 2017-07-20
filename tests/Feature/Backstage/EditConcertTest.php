<?php
namespace Tests\Feature\Backstage;
use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到自己還沒發佈的音樂會修改頁()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者不能看到自己已經發佈的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(403);
    }

    public function test_管理者不能看到別人的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(404);
    }

    public function test_管理者不能看到不存在的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");
        $response->assertStatus(404);
    }

    public function test_一般使用者要看任何音樂會修改頁都必須導到登入頁()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $response = $this->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_一般使用者要看任何音樂會修改頁都必須導到登入頁即使音樂會不存在()
    {
        $response = $this->get("/backstage/concerts/999/edit");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_管理者可以編輯自己尚未發布的音樂會()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('new title', $concert->title);
            $this->assertEquals('new subtitle', $concert->subtitle);
            $this->assertEquals('new additional_information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
            $this->assertEquals('new venue', $concert->venue);
            $this->assertEquals('new venue_address', $concert->venue_address);
            $this->assertEquals('new city', $concert->city);
            $this->assertEquals('new state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }

    public function test_管理者不能編輯別人尚未發布的音樂會()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $otherUser->id,
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertStatus(404);
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('old title', $concert->title);
            $this->assertEquals('old subtitle', $concert->subtitle);
            $this->assertEquals('old additional_information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('old venue', $concert->venue);
            $this->assertEquals('old venue_address', $concert->venue_address);
            $this->assertEquals('old city', $concert->city);
            $this->assertEquals('old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    public function test_管理者不能編輯尚未發布的音樂會()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create([
            'user_id'                => $user->id,
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
        ]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertStatus(403);
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('old title', $concert->title);
            $this->assertEquals('old subtitle', $concert->subtitle);
            $this->assertEquals('old additional_information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('old venue', $concert->venue);
            $this->assertEquals('old venue_address', $concert->venue_address);
            $this->assertEquals('old city', $concert->city);
            $this->assertEquals('old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    public function test_一般使用者不能編輯音樂會()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertRedirect('/login');
        tap($concert->fresh(), function($concert) {
            $this->assertEquals('old title', $concert->title);
            $this->assertEquals('old subtitle', $concert->subtitle);
            $this->assertEquals('old additional_information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('old venue', $concert->venue);
            $this->assertEquals('old venue_address', $concert->venue_address);
            $this->assertEquals('old city', $concert->city);
            $this->assertEquals('old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }
}
