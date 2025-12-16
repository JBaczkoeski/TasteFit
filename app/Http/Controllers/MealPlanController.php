<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Services\MealPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealPlanController extends Controller
{
    public function __construct(readonly MealPlanService $mealPlanService)
    {
    }

    public function index()
    {
        $mealPlans = auth()->user()->mealPlans;

        return Inertia::render(
            'Meal/Plans/Index', [
                'mealPlans' => $mealPlans
            ]
        );
    }

    public function show(MealPlan $mealPlan)
    {
        $mealPlan->load([
            'mealPlanDay' => fn($q) => $q->orderBy('day_number'),
            'mealPlanDay.mealPlanDayMeal' => fn($q) => $q->orderBy('position'),
            'mealPlanDay.mealPlanDayMeal.meal',
            'mealPlanDay.mealPlanDayMeal.meal.ingredients.ingredient',
            'mealPlanDay.shoppingListItems',
            'mealPlanDay.mealPlanDayMeal.meal.ingredients',
            'mealPlanDay.shoppingListItems.ingredient',
        ]);

        return Inertia::render('MealPlans/Show', [
            'plan' => $mealPlan,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $settings = $user->mealPlanSettings;

        $defaults = [
            'calories' => $settings->default_calories ?? 2000,
            'duration' => $settings->default_duration ?? 7,
            'meals' => 5,
            'cuisines' => $settings->preferred_cuisines ?? [],
            'diet' => ($settings->preferred_diets[0] ?? 'None') ?? 'None',
            'difficulty' => $settings->difficulty ?? 'Normal',
        ];

        return Inertia::render('Meal/Plans/Create', [
            'defaults' => $defaults,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'calories' => 'required|integer',
                'diet' => 'nullable|string',
                'duration' => 'required|integer',
                'difficulty' => 'nullable|string',
                'cuisines' => 'nullable|array',
                'cuisines.*' => 'string',
                'name' => 'string',
                'meals' => 'required|integer|min:1|max:6',
            ]
        );

        $plan = $this->mealPlanService->generateCustomPlan(
            [
                'calories' => $validated['calories'],
                'diet' => $validated['diet'] !== 'None' ? $validated['diet'] : null,
                'duration' => $validated['duration'],
                'difficulty' => $validated['difficulty'] ?? 'Normal',
                'cuisines' => $validated['cuisines'] ?? [],
                'meal' => $validated['meals'],
            ]
        );

        $mealPlan = $this->mealPlanService->storePlanToDatabase($plan, $validated, auth()->id());

        return response()->json($plan);
    }
}
