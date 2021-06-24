<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('avatar.png');
            $table->string('email')->unique();
            $table->string('password');
        });

        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('barber_id');
        });

        Schema::create('user_appointments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('barber_id');
            $table->integer('barber_service_id');
            $table->datetime('appointment_datetime');
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('avatar.png');
            $table->float('stars')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });

        Schema::create('barber_photos', function (Blueprint $table) {
            $table->id();
            $table->integer('barber_id');
            $table->string('url');
        });

        Schema::create('barber_reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('barber_id');
            $table->float('rate');
        });

        Schema::create('barber_services', function (Blueprint $table) {
            $table->id();
            $table->integer('barber_id');
            $table->string('name');
            $table->float('price');
        });

        Schema::create('barber_testimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('barber_id');
            $table->string('name');
            $table->float('rate');
            $table->text('body');
        });

        Schema::create('barber_availabilities', function (Blueprint $table) {
            $table->id();
            $table->integer('barber_id');
            $table->string('week_day');
            $table->string('hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_favorites');
        Schema::dropIfExists('user_appointments');
        Schema::dropIfExists('barbers');
        Schema::dropIfExists('barber_photos');
        Schema::dropIfExists('barber_reviews');
        Schema::dropIfExists('barber_services');
        Schema::dropIfExists('barber_testimonials');
        Schema::dropIfExists('barber_avalibilities');
    }
}
