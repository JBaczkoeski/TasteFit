<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meal_plan_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedSmallInteger('default_calories')->nullable();
            $table->unsignedTinyInteger('default_duration')->default(7);

            $table->json('preferred_diets')->nullable();
            $table->json('preferred_cuisines')->nullable();

            $table->string('difficulty', 20)->default('Normal');
            $table->boolean('notifications')->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plan_settings');
    }
};
