<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'meals', function (Blueprint $table) {
                $table->id();
                $table->integer('spoonacular_id');
                $table->string('title');
                $table->string('type');
                $table->string('diet');
                $table->longText('instructions');
                $table->integer('ready_in_minutes');
                $table->integer('calories');
                $table->string('image');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('meal');
    }
};
