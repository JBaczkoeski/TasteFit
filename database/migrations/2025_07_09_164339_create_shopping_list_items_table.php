<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'shopping_list_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meal_plan_day_id');
                $table->foreignId('ingredient_id');
                $table->float('total_amount');
                $table->string('unit');
                $table->json('meta');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
    }
};
