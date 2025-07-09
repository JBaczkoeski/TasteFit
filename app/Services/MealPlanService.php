<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Meal;
use App\Models\MealIngredient;
use App\Models\MealPlan;
use Illuminate\Support\Facades\Http;

class MealPlanService
{
    public function generateCustomPlan(array $options): array
    {
        $plan = [];

        $defaultTypes = ['breakfast', 'second breakfast', 'lunch', 'afternoon snack', 'dinner', 'evening snack'];
        $mealTypes = array_slice($defaultTypes, 0, $options['meal']);
        $caloriesMap = [];
        $total = $options['calories'];

        if (count($mealTypes) >= 3) {
            $lunchBonus = $total * 0.10; // np. lunch +10%
            $base = ($total - $lunchBonus) / count($mealTypes);
            foreach ($mealTypes as $type) {
                $caloriesMap[$type] = $type === 'lunch' ? $base + $lunchBonus : $base;
            }
        } else {
            $equal = $total / count($mealTypes);
            foreach ($mealTypes as $type) {
                $caloriesMap[$type] = $equal;
            }
        }

        for ($day = 1; $day <= $options['duration']; $day++) {
            $dayMeals = [];

            foreach ($mealTypes as $type) {
                $meal = $this->fetchMeal($options, $type, $caloriesMap[$type]);

                if ($meal) {
                    $dayMeals[] = [
                        'type' => ucfirst($type),
                        'title' => $meal['title'],
                        'id' => $meal['id'],
                        'readyInMinutes' => $meal['readyInMinutes'],
                        'calories' => $this->extractCalories($meal),
                        'ingredients' => $meal['extendedIngredients'] ?? [],
                        'instructions' => $meal['instructions'] ?? null,
                    ];
                }
            }

            $plan[] = [
                'day' => $day,
                'meal' => $dayMeals,
            ];
        }

        return $plan;
    }

    private function fetchMeal(array $options, string $type, int $maxCalories): ?array
    {
        $query = [
            'diet' => $options['diet'] !== 'None' ? $options['diet'] : null,
            'cuisine' => implode(',', $options['cuisines']),
            'type' => $type,
            'number' => 5,
            'instructionsRequired' => true,
            'addRecipeNutrition' => true,
            'maxCalories' => $maxCalories,
            'minCalories' => $maxCalories * 0.9,
        ];

        $response = Http::withHeaders([
            'x-api-key' => config('services.spoonacular.key'),
        ])->get('https://api.spoonacular.com/recipes/complexSearch', array_filter($query));

        $data = $response->json();

        if (empty($data['results'])) {
            return null;
        }

        foreach ($data['results'] as $recipe) {
            $info = $this->getRecipeInformation($recipe['id']);

            if (! $info) {
                continue;
            }

            if ($this->checkDifficulty($info['readyInMinutes'], $options['difficulty'])) {
                return array_merge($recipe, $info);
            }
        }

        return null;
    }

    private function getRecipeInformation(int $id): ?array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.spoonacular.key'),
        ])->get("https://api.spoonacular.com/recipes/{$id}/information", [
            'includeNutrition' => true,
        ]);

        return $response->successful() ? $response->json() : null;
    }

    private function checkDifficulty(int $minutes, string $level): bool
    {
        return match ($level) {
            'Easy' => $minutes <= 25,
            'Normal' => $minutes <= 45,
            default => true
        };
    }

    private function extractCalories(array $meal): ?int
    {
        if (isset($meal['nutrition']['nutrients'])) {
            foreach ($meal['nutrition']['nutrients'] as $nutrient) {
                if (strtolower($nutrient['name']) === 'calories') {
                    return round($nutrient['amount']);
                }
            }
        }

        return null;
    }

    public function storePlanToDatabase(array $generatedPlan, array $options, int $userId): \App\Models\MealPlan
    {
        $mealPlan = MealPlan::create([
            'user_id' => $userId,
            'title' => $options['title'] ?? 'Custom Meal Plan',
            'total_days' => $options['duration'],
            'daily_calories' => $options['calories'],
            'daily_meals' => $options['meals'],
            'diet_type' => $options['diet'],
            'plan_difficulty' => $options['difficulty'],
            'cuisines' => $options['cuisines'],
        ]);

        foreach ($generatedPlan as $dayData) {
            $day = $mealPlan->mealPlanDay()->create([
                'day_number' => $dayData['day'],
                'total_calories' => collect($dayData['meal'])->sum('calories'),
            ]);

            foreach ($dayData['meal'] as $mealData) {
                $meal = Meal::firstOrCreate(
                    ['spoonacular_id' => $mealData['id']],
                    [
                        'title' => $mealData['title'],
                        'type' => strtolower($mealData['type']),
                        'ready_in_minutes' => $mealData['readyInMinutes'],
                        'calories' => $mealData['calories'],
                        'instructions' => $mealData['instructions'],
                    ]
                );

                foreach ($mealData['ingredients'] as $ingredientData) {
                    $ingredient = Ingredient::firstOrCreate(
                        ['spoonacular_id' => $ingredientData['id']],
                        [
                            'name' => $ingredientData['name'],
                            'name_clean' => $ingredientData['nameClean'],
                            'image' => $ingredientData['image'] ?? null,
                            'aisle' => $ingredientData['aisle'] ?? null,
                        ]
                    );

                    MealIngredient::firstOrCreate([
                        'meal_id' => $meal->id,
                        'ingredient_id' => $ingredient->id,
                        'amount' => $ingredientData['amount'],
                        'unit' => $ingredientData['unit'],
                        'amount_metric' => $ingredientData['measures']['metric']['amount'] ?? null,
                        'unit_metric' => $ingredientData['measures']['metric']['unitShort'] ?? null,
                        'original' => $ingredientData['original'],
                        'meta' => json_encode($ingredientData['meta']),
                    ]);
                }

                $day->mealPlanDayMeal()->create([
                    'meal_id' => $meal->id,
                    'meal_type' => $mealData['type'],
                    'position' => null,
                ]);
            }
        }

        return $mealPlan;
    }
}
