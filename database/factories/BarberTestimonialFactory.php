<?php

namespace Database\Factories;

use App\Models\BarberTestimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberTestimonialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BarberTestimonial::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'barber_id' => rand(1, 15),
            'name' => $this->faker->name(),
            'rate' => rand(2, 4) . '.' . rand(0, 9),
            'body' => $this->faker->text(),
        ];
    }
}
