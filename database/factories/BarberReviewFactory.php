<?php

namespace Database\Factories;

use App\Models\BarberReview;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BarberReview::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'barber_id' => rand(1, 15),
            'rate'      => rand(2, 4) . '.' . rand(0, 9),
        ];
    }
}
