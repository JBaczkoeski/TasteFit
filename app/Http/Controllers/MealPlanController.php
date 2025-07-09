<?php

namespace App\Http\Controllers;

use App\Services\MealPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MealPlanController extends Controller
{
    public function __construct(readonly MealPlanService $mealPlanService) {}

    public function index()
    {
        return Inertia::render('Meal/Plans/Index');
    }

    public function create()
    {
        return Inertia::render('Meal/Plans/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'calories' => 'required|integer',
            'diet' => 'nullable|string',
            'duration' => 'required|integer',
            'difficulty' => 'nullable|string',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'string',
            'meals' => 'required|integer|min:1|max:6',
        ]);

        $plan = $this->mealPlanService->generateCustomPlan([
            'calories' => $validated['calories'],
            'diet' => $validated['diet'] !== 'None' ? $validated['diet'] : null,
            'duration' => $validated['duration'],
            'difficulty' => $validated['difficulty'] ?? 'Normal',
            'cuisines' => $validated['cuisines'] ?? [],
            'meal' => $validated['meals'],
        ]);

        $mealPlan = $this->mealPlanService->storePlanToDatabase($plan, $validated, auth()->id());

        return response()->json($plan);
    }
}
