<?php

namespace Database\Factories;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ConcertFactory
 * @package Database\Factories
 */
class ConcertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concert::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => function() {
                return User::factory()->create()->id;
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
    }

    public function published(): ConcertFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => Carbon::parse('-1 week'),
            ];
        });
    }

    public function unpublished(): ConcertFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }

    /**
     * @param  array  $overrides
     * @return Collection|Model|mixed
     */
    public static function createPublished($overrides = [])
    {
        $concert = Concert::factory()->create($overrides);
        $concert->publish();
        return $concert;
    }

    /**
     * @param  array  $overrides
     * @return Collection|Model|mixed
     */
    public static function createUnpublished($overrides = [])
    {
        return Concert::factory()->create($overrides);
    }
}
