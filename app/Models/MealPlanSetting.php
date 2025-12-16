<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlanSetting extends Model
{
    protected $fillable = [
        'user_id',
        'default_calories',
        'default_duration',
        'preferred_diets',
        'preferred_cuisines',
        'difficulty',
        'notifications',
    ];

    protected $casts = [
        'preferred_diets' => 'array',
        'preferred_cuisines' => 'array',
        'notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
