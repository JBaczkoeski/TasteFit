<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meal_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('title');
                $table->integer('total_days');
                $table->integer('daily_calories');
                $table->integer('daily_meals');
                $table->string('diet_type');
                $table->string('plan_difficulty');
                $table->json('cuisines');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
