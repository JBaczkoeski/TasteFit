<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'total_days',
        'daily_calories',
        'daily_meals',
        'diet_type',
        'plan_difficulty',
        'cuisines',
    ];

    protected $appends = ['created_at_human'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlanDay(): HasMany
    {
        return $this->hasMany(MealPlanDay::class);
    }

    protected function casts(): array
    {
        return [
            'cuisines' => 'array',
        ];
    }

    protected function createdAtHuman(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at?->translatedFormat('d.m.Y')
        );
    }
}
