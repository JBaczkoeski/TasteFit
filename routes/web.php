<?php

use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\MealPlanHistoryController;
use App\Http\Controllers\MealPlanSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShoppingListController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/meal/plans', [MealPlanController::class, 'index'])->name('plan.meal.index');
    Route::get('/meal/plan/{mealPlan}', [MealPlanController::class, 'show'])->name('plan.meal.show');
    Route::get('/create/meal/plans', [MealPlanController::class, 'create'])->name('plan.meal.create');
    Route::post('/generate/meal/plans', [MealPlanController::class, 'store'])->name('plan.meal.store');

    Route::get('/shopping/list', [ShoppingListController::class, 'index'])->name('shopping.list.index');

    Route::get('/history', [MealPlanHistoryController::class, 'index'])->name('meal.history.index');

    Route::get('/meal/plans/settings', [MealPlanSettingsController::class, 'index'])->name('meal.plans.settings.index');

    Route::post('/meal/plans/settings', [MealPlanSettingsController::class, 'update'])->name('meal.plans.settings.update');
});

require __DIR__.'/auth.php';
