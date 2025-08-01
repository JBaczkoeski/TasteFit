<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meal_plan_day_meals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meal_plan_day_id');
                $table->foreignId('meal_id');
                $table->string('meal_type');
                $table->integer('position');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_day_meals');
    }
};
