<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlanDay extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'meal_plan_id',
        'day_number',
        'total_calories',
    ];

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function mealPlanDayMeal(): HasMany
    {
        return $this->hasMany(MealPlanDayMeal::class);
    }

    public function shoppingListItems(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }
}
