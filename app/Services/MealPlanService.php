<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MealPlanService
{
    public function generateCustomPlan(array $options): array
    {
        $plan = [];

        $mealTypes = ['breakfast', 'second breakfast', 'lunch', 'afternoon snack', 'dinner'];
        $caloriesPerMeal = $options['calories'] / count($mealTypes);

        for ($day = 1; $day <= $options['duration']; $day++) {
            $dayMeals = [];

            foreach ($mealTypes as $type) {
                $meal = $this->fetchMeal($options, $type, $caloriesPerMeal);

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
                'meals' => $dayMeals
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
            'sort' => 'random'
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

            if (!$info) {
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
            'includeNutrition' => true
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
}
