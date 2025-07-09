<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'spoonacular_id',
        'name',
        'image',
        'aisle',
    ];

    public function mealIngredient(): HasMany
    {
        return $this->hasMany(MealIngredient::class);
    }
}
