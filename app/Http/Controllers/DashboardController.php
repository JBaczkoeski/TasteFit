<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $currentPlan = MealPlan::query()
            ->where('user_id', $userId)
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->first();

        $todayMeals = [];
        $todayShopping = [];
        $planImages = [];
        $todayDayNumber = null;

        if ($currentPlan) {
            $currentPlan->load([
                'mealPlanDay' => fn ($q) => $q->orderBy('day_number'),
                'mealPlanDay.mealPlanDayMeal' => fn ($q) => $q->orderBy('position'),
                'mealPlanDay.mealPlanDayMeal.meal',
                'mealPlanDay.shoppingListItems.ingredient',
            ]);

            $todayDayNumber = $this->resolveTodayDayNumber($currentPlan);

            $todayDay = $currentPlan->mealPlanDay->firstWhere('day_number', $todayDayNumber)
                ?? $currentPlan->mealPlanDay->first();

            if ($todayDay) {
                $todayMeals = $todayDay->mealPlanDayMeal->map(function ($m) {
                    return [
                        'id' => (int) $m->id,
                        'meal_id' => (int) $m->meal_id,
                        'meal_type' => (string) $m->meal_type,
                        'title' => (string) ($m->meal?->title ?? '—'),
                        'calories' => (int) ($m->meal?->calories ?? 0),
                        'ready_in_minutes' => (int) ($m->meal?->ready_in_minutes ?? 0),
                        'image' => (string) ($m->meal?->image ?? ''),
                    ];
                })->values()->all();

                $todayShopping = $todayDay->shoppingListItems
                    ->groupBy(fn ($it) => ((int) $it->ingredient_id).'|'.((string) $it->unit))
                    ->map(function ($group) {
                        $first = $group->first();
                        return [
                            'ingredient_id' => (int) $first->ingredient_id,
                            'name' => (string) ($first->ingredient?->name ?? '—'),
                            'image' => (string) ($first->ingredient?->image ?? ''),
                            'unit' => (string) ($first->unit ?? ''),
                            'amount' => (float) $group->sum(fn ($x) => (float) $x->total_amount),
                        ];
                    })
                    ->values()
                    ->sortBy('name')
                    ->values()
                    ->all();
            }

            $planImages = collect($currentPlan->mealPlanDay)
                ->flatMap(fn ($d) => $d->mealPlanDayMeal)
                ->map(fn ($m) => (string) ($m->meal?->image ?? ''))
                ->filter(fn ($img) => $img !== '')
                ->unique()
                ->values()
                ->take(8)
                ->all();
        }

        $history = MealPlan::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($plan) => [
                'id' => (int) $plan->id,
                'created_at' => $plan->created_at?->toDateString(),
                'daily_calories' => (int) $plan->daily_calories,
                'total_days' => (int) $plan->total_days,
                'status' => (string) $plan->status,
                'title' => (string) $plan->title,
            ])
            ->values()
            ->all();

        return Inertia::render('Dashboard', [
            'currentPlan' => $currentPlan,
            'todayDayNumber' => $todayDayNumber,
            'todayMeals' => $todayMeals,
            'todayShopping' => $todayShopping,
            'planImages' => $planImages,
            'history' => $history,
        ]);
    }

    private function resolveTodayDayNumber(MealPlan $plan): int
    {
        $start = $plan->created_at ? Carbon::parse($plan->created_at)->startOfDay() : now()->startOfDay();
        $today = now()->startOfDay();

        $diff = $start->diffInDays($today);
        $day = $diff + 1;

        $day = max(1, $day);
        $day = min((int) $plan->total_days, $day);

        return $day;
    }
}
