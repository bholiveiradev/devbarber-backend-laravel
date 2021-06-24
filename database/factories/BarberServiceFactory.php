<?php

namespace Database\Factories;

use App\Models\BarberService;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BarberService::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'barber_id' => rand(1, 15),
            'name'  => $this->faker->sentence(2),
            'price' => $this->faker->numberBetween(10, 150),
        ];
    }
}
