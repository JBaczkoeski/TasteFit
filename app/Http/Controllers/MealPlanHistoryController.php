<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use Inertia\Inertia;

class MealPlanHistoryController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $plans = MealPlan::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'daily_calories' => $plan->daily_calories,
                    'total_days' => $plan->total_days,
                    'created_at' => $plan->created_at->format('d-m-Y'),
                    'status' => $plan->status,
                ];
            });

        return Inertia::render('Meal/History', [
            'plans' => $plans,
        ]);
    }
}
