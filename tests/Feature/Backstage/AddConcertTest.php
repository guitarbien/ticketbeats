<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function from($url)
    {
        session()->setPreviousUrl($url);
        return $this;
    }

    public function test_管理者可以看到新增音樂會的表單新增頁()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    public function test_guests不能看到新增音樂會的表單新增頁()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_管理者可以加入一個合法的音樂會()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Fake St.',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);

        tap(Concert::first(), function($concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }

    public function test_geusts不能新增音樂會()
    {
        $response = $this->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Fake St.',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/login");
        $this->assertEquals(0, Concert::count());
    }

    public function test_title欄位為必填()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title'                  => '',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Fake St.',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    public function test_subtitle欄位為非必填()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => '',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Fake St.',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);

        tap(Concert::first(), function($concert) use ($response) {
            $response->assertStatus(302);
            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertNull($concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }
}
