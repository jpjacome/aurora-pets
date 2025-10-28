<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('pet_name')->nullable();
            $table->string('pet_species')->nullable();
            $table->string('gender')->nullable();
            $table->date('pet_birthday')->nullable();
            $table->string('pet_breed')->nullable();
            $table->string('pet_weight')->nullable();
            $table->json('pet_color')->nullable();
            $table->string('living_space')->nullable();
            $table->json('pet_characteristics')->nullable();
            $table->string('plant_test')->nullable();
            $table->string('plant')->nullable();
            $table->text('plant_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
