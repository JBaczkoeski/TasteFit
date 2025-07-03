<?php

use App\Http\Controllers\ProfileController;
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

    Route::get('/meal/plans', [\App\Http\Controllers\MealPlanController::class, 'index'])->name('plan.meal.index');
    Route::get('/create/meal/plans', [\App\Http\Controllers\MealPlanController::class, 'create'])->name('plan.meal.create');
    Route::post('/generate/meal/plans', [\App\Http\Controllers\MealPlanController::class, 'store'])->name('plan.meal.store');

    Route::get('/shopping/list', [\App\Http\Controllers\ShoppingListController::class, 'index'])->name('shopping.list.index');

    Route::get('/history', [\App\Http\Controllers\MealPlanHistoryController::class, 'index'])->name('meal.history.index');

    Route::get('/meal/plans/settings', [\App\Http\Controllers\MealPlanSettingsController::class, 'index'])->name('meal.plans.settings.index');
});

require __DIR__.'/auth.php';
