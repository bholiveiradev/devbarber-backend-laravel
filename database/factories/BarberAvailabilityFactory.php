<?php

namespace Database\Factories;

use App\Models\BarberAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberAvailabilityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BarberAvailability::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $hours = [];

        for ($i = 0; $i < 4; $i++) {
            $rAdd   = rand(7, 10);
            $hours  = [];

            for ($r = 0; $r < 8; $r++) {
                $time = $r + $rAdd;

                if ($time < 10) {
                    $time = '0' . $time;
                }

                $hours[] = $time . ':00';
            }
        }
        return [
            'barber_id' => rand(1, 15),
            'week_day'  => rand(0, 4),
            'hours'      => implode(',', $hours),
        ];
    }
}
