<?php

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->create([
            'email'    => 'bien@example.com',
            'password' => bcrypt('secret'),
        ]);

        factory(Concert::class)->states('published')->create([
            'user_id' => $user->id,
            'title' => "The Red Chord",
            'subtitle' => "with Animosity and Lethargy",
            'venue' => "The Mosh Pit",
            'venue_address' => "123 Example Lane",
            'city' => "Laraville",
            'state' => "ON",
            'zip' => "17916",
            'date' => Carbon::parse('2017-09-13 8:00pm'),
            'ticket_price' => 3250,
            'additional_information' => "This concert is 19+.",
        ])->addTickets(10);

        factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => "Slayer",
            'subtitle' => "with Forbidden and Testament",
            'venue' => "The Rock Pile",
            'venue_address' => "55 Sample Blvd",
            'city' => "Laraville",
            'state' => "ON",
            'zip' => "19276",
            'date' => Carbon::parse('2017-10-05 7:00pm'),
            'ticket_price' => 5500,
            'additional_information' => null,
        ])->addTickets(10);
    }
}
