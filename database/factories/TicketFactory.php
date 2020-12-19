<?php

namespace Database\Factories;

use App\Concert;
use App\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class TicketFactory
 * @package Database\Factories
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'concert_id' => function () {
                return Concert::factory()->create()->id;
            },
        ];
    }

    public function reserved(): TicketFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'reserved_at' => now(),
            ];
        });
    }
}
