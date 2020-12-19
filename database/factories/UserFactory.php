<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class UserFactory
 * @package Database\Factories
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$TIjoYBhzPy3Kf2QdAMuOyeECGp9YqYyRWheVtJjT/qfZv.YYBcLD6', // pre hash `bcrypt('secret')`
            'remember_token' => \Illuminate\Support\Str::random(10),
            'stripe_account_id' => 'test_acct_1234',
            'stripe_access_token' => 'test_token',
        ];
    }
}
