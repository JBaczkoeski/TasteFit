<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class shopping_list_items extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'meal_plan_day_id',
        'ingredient_id',
        'total_amount',
        'unit',
        'meta',
    ];

    public function mealPlanDay(): BelongsTo
    {
        return $this->belongsTo(MealPlanDay::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}
