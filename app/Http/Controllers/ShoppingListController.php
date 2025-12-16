<?php

namespace App\Http\Controllers;

use App\Models\MealPlan;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ShoppingListController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $plan = MealPlan::with([
            'mealPlanDay.shoppingListItems.ingredient',
        ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return Inertia::render('ShoppingList/Index', [
            'plan' => $plan,
        ]);
    }
}
