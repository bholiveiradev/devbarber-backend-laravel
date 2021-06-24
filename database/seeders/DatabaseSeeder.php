<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\BarberAvailability;
use App\Models\BarberPhoto;
use App\Models\BarberReview;
use App\Models\BarberService;
use App\Models\BarberTestimonial;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Barber::factory(15)->create();
        BarberPhoto::factory(15)->create();
        BarberReview::factory(20)->create();
        BarberService::factory(15)->create();
        BarberTestimonial::factory(50)->create();
        BarberAvailability::factory(50)->create();
    }
}
