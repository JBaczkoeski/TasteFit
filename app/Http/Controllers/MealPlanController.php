<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\MealPlanService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class MealPlanController extends Controller
{
    public function __construct(readonly MealPlanService $mealPlanService)
    {}

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
        ]);

        $plan = $this->mealPlanService->generateCustomPlan([
            'calories' => $validated['calories'],
            'diet' => $validated['diet'] !== 'None' ? $validated['diet'] : null,
            'duration' => $validated['duration'],
            'difficulty' => $validated['difficulty'] ?? 'Normal',
            'cuisines' => $validated['cuisines'] ?? [],
        ]);

        return response()->json($plan);
    }
}
