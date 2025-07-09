<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meal_ingredients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meal_id');
                $table->foreignId('ingredient_id');
                $table->float('amount');
                $table->string('unit');
                $table->float('amount_metric');
                $table->string('unit_metric');
                $table->string('original');
                $table->json('meta');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_ingredients');
    }
};
