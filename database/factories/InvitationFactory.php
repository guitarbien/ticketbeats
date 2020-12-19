<?php

namespace Database\Factories;

use App\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class InvitationFactory
 * @package Database\Factories
 */
class InvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => 'somebody@example.com',
            'code'  => 'TESTCODE1234',
        ];
    }
}
