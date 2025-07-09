<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meal_plan_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meal_plan_id');
                $table->integer('day_number');
                $table->integer('total_calories');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_days');
    }
};
