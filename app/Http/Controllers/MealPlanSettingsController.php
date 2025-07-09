<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MealPlanSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings');
    }
}
