<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlanDayMeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'meal_plan_day_id',
        'meal_id',
        'meal_type',
        'position',
    ];

    public function mealPlanDay(): BelongsTo
    {
        return $this->belongsTo(MealPlanDay::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
