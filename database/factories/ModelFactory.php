<?php

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TIjoYBhzPy3Kf2QdAMuOyeECGp9YqYyRWheVtJjT/qfZv.YYBcLD6', // pre hash `bcrypt('secret')`
        'remember_token' => str_random(10),
        'stripe_account_id' => 'test_acct_1234',
        'stripe_access_token' => 'test_token',
    ];
});

$factory->define(App\Concert::class, function(Faker\Generator $faker) {
    return [
        'user_id'                => function() {
            return factory(App\User::class)->create()->id;
        },
        'title'                  => 'sample title',
        'subtitle'               => 'sample subtitle',
        'additional_information' => 'sample additional_information',
        'date'                   => Carbon::parse('2016-12-01 8:00pm'),
        'venue'                  => 'sample venue',
        'venue_address'          => 'sample venue_address',
        'city'                   => 'sample city',
        'state'                  => 'sample state',
        'zip'                    => 'sample zip',
        'ticket_price'           => 5566,
        'ticket_quantity'        => 5,
    ];
});

$factory->state(App\Concert::class, 'published', function(Faker\Generator $faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
    ];
});

$factory->state(App\Concert::class, 'unpublished', function(Faker\Generator $faker) {
    return [
        'published_at' => null,
    ];
});

$factory->define(App\Ticket::class, function(Faker\Generator $faker) {
    return [
        'concert_id' => function () {
            return factory(App\Concert::class)->create()->id;
        },
    ];
});

$factory->state(App\Ticket::class, 'reserved', function ($faker) {
    return [
        'reserved_at' => Carbon::now(),
    ];
});

$factory->define(App\Order::class, function(Faker\Generator $faker) {
    return [
        'amount' => 5250,
        'email' => 'somebody@example.com',
        'confirmation_number' => 'ORDERCONFIRMTION1234',
        'card_last_four' => '1234',
    ];
});

$factory->define(App\Invitation::class, function(Faker\Generator $faker) {
    return [
        'email' => 'somebody@example.com',
        'code'  => 'TESTCODE1234',
    ];
});