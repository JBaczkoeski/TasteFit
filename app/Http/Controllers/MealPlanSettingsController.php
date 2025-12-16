<?php

namespace App\Http\Controllers;

use App\Models\MealPlanSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealPlanSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $settings = $user->mealPlanSettings
            ?: new MealPlanSetting([
                'default_calories' => 2000,
                'default_duration' => 7,
                'preferred_diets' => ['Vegetarian'],
                'preferred_cuisines' => ['Italian', 'Asian'],
                'difficulty' => 'Normal',
                'notifications' => true,
            ]);

        return Inertia::render('Settings', [
            'settings' => $settings,
            'dietOptions' => ['None', 'Vegetarian', 'Vegan', 'Keto', 'Gluten-Free'],
            'cuisineOptions' => ['Italian', 'Asian', 'Mediterranean', 'Mexican', 'Indian'],
            'difficultyOptions' => ['Easy', 'Normal', 'Advanced'],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'defaultCalories' => 'required|integer|min:800|max:6000',
            'defaultDuration' => 'required|integer|min:1|max:60',
            'preferredDiets' => 'array',
            'preferredDiets.*' => 'string|max:50',
            'preferredCuisines' => 'array',
            'preferredCuisines.*' => 'string|max:50',
            'difficulty' => 'required|string|in:Easy,Normal,Advanced',
            'notifications' => 'boolean',
        ]);

        $user = $request->user();

        $user->mealPlanSettings()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'default_calories' => $data['defaultCalories'],
                'default_duration' => $data['defaultDuration'],
                'preferred_diets' => $data['preferredDiets'] ?? [],
                'preferred_cuisines' => $data['preferredCuisines'] ?? [],
                'difficulty' => $data['difficulty'],
                'notifications' => $data['notifications'] ?? false,
            ]
        );

        return back()->with('success', 'Preferences saved.');
    }
}
