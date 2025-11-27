<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'spoonacular_id',
        'title',
        'type',
        'instructions',
        'ready_in_minutes',
        'calories',
        'image',
        'diet',
        'cuisine',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(MealIngredient::class, 'meal_id');
    }

    public function mealIngredients(): HasMany
    {
        return $this->hasMany(MealIngredient::class, 'meal_id');
    }
}
