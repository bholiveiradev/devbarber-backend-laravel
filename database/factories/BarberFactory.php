<?php

namespace Database\Factories;

use App\Models\Barber;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Barber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'   => $this->faker->name(),
            'avatar' => 'avatar.png',
            'stars'  => rand(0, 5),
            'latitude'  => '-23.5' . rand(0, 9) . '30907',
            'longitude' => '-46.6' . rand(0, 9) . '82795',
        ];
    }
}
