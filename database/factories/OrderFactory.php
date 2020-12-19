<?php

namespace Database\Factories;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderFactory
 * @package Database\Factories
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'amount' => 5250,
            'email' => 'somebody@example.com',
            'confirmation_number' => 'ORDERCONFIRMTION1234',
            'card_last_four' => '1234',
        ];
    }

    /**
     * @param  Concert  $concert
     * @param  array  $overrides
     * @param  int  $ticketQuantity
     * @return Collection|Model|mixed
     */
    public static function createForConcert(Concert $concert, array $overrides = [], int $ticketQuantity = 1)
    {
        $order = Order::factory()->create($overrides);
        $tickets = Ticket::factory()->count($ticketQuantity)->create(['concert_id' => $concert->id]);
        $order->tickets()->saveMany($tickets);
        return $order;
    }
}
