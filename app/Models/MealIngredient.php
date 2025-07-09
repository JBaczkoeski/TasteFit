<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealIngredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'meal_id',
        'ingredient_id',
        'amount',
        'unit',
        'amount_metric',
        'unit_metric',
        'original',
        'meta',
    ];

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
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
