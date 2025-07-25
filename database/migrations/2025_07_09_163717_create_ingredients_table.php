<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ingredients', function (Blueprint $table) {
                $table->id();
                $table->integer('spoonacular_id');
                $table->string('name');
                $table->string('image');
                $table->string('aisle');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
