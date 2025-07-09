<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MealPlanHistoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Meal/History');
    }
}
