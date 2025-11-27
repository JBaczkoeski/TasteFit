<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Meal;
use App\Models\MealIngredient;
use App\Models\MealPlan;
use Illuminate\Support\Facades\Http;

class MealPlanService
{
    private array $usedRecipeIds = [];
    private array $recipeLastUsedDay = [];

    public function generateCustomPlan(array $options): array
    {
        $plan = [];
        $pairs = $this->allowedMealTypes($options);
        $targets = $this->caloriesDistribution((int)$options['calories'], count($pairs));

        for ($day = 1; $day <= (int)$options['duration']; $day++) {
            $dayMeals = [];

            for ($i = 0; $i < count($pairs); $i++) {
                [$userType, $apiType] = $pairs[$i];
                $kcalTarget = $targets[$i];

                $meal = $this->fetchMeal($options, $userType, $apiType, $kcalTarget, $day);

                if ($meal) {
                    $dayMeals[] = [
                        'type' => $userType,
                        'title' => $meal['title'],
                        'id' => $meal['id'],
                        'readyInMinutes' => $meal['readyInMinutes'] ?? null,
                        'calories' => $this->extractCalories($meal),
                        'ingredients' => $meal['extendedIngredients'] ?? [],
                        'instructions' => $meal['instructions'] ?? null,
                        'image' => $meal['image'] ?? null,
                        'diet' => $meal['diet'] ?? $this->baseDietFromOptions($options),
                        'diet_type' => $this->resolveDietType($meal, $options),
                        'cuisine' => $meal['cuisine'] ?? (
                            isset($meal['cuisines']) && is_array($meal['cuisines']) && count($meal['cuisines'])
                                ? implode(',', $meal['cuisines'])
                                : null
                            ),
                    ];
                }
            }

            $need = (int)$options['calories'] - (int)collect($dayMeals)->sum('calories');
            if ($need > (int)round($options['calories'] * 0.08)) {
                $booster = $this->fetchMeal($options, 'evening snack', 'snack', $need, $day);

                if ($booster) {
                    $dayMeals[] = [
                        'type' => 'evening snack',
                        'title' => $booster['title'],
                        'id' => $booster['id'],
                        'readyInMinutes' => $booster['readyInMinutes'] ?? null,
                        'calories' => $this->extractCalories($booster),
                        'ingredients' => $booster['extendedIngredients'] ?? [],
                        'instructions' => $booster['instructions'] ?? null,
                        'image' => $booster['image'] ?? null,
                        'diet' => $booster['diet'] ?? $this->baseDietFromOptions($options),
                        'diet_type' => $this->resolveDietType($booster, $options),
                        'cuisine' => $booster['cuisine'] ?? (
                            isset($booster['cuisines']) && is_array($booster['cuisines']) && count($booster['cuisines'])
                                ? implode(',', $booster['cuisines'])
                                : null
                            ),
                    ];
                }
            }

            $plan[] = [
                'day' => $day,
                'meal' => array_slice($dayMeals, 0, count($pairs)),
            ];
        }

        return $plan;
    }

    private function fetchMeal(
        array  $options,
        string $userType,
        string $apiType,
        int    $targetKcal,
        int    $day
    ): ?array {
        $strategies = [
            ['wider' => false, 'dropCuisine' => false],
            ['wider' => true,  'dropCuisine' => false],
            ['wider' => true,  'dropCuisine' => true],
        ];

        foreach ($strategies as $s) {
            $meal = $this->fetchMealFromDatabase(
                $options,
                $userType,
                $targetKcal,
                $s['wider'],
                $s['dropCuisine'],
                $day
            );

            if ($meal) {
                return $meal;
            }
        }

        foreach ($strategies as $s) {
            $meal = $this->fetchMealFromApi(
                $options,
                $userType,
                $apiType,
                $targetKcal,
                $s['wider'],
                $s['dropCuisine'],
                $day
            );

            if ($meal) {
                return $meal;
            }
        }

        return null;
    }

    private function fetchMealFromApi(
        array  $options,
        string $userType,
        string $apiType,
        int    $targetKcal,
        bool   $wider,
        bool   $dropCuisine,
        int    $day
    ): ?array {
        $cuisines = $options['cuisines'] ?? [];
        if (!is_array($cuisines)) {
            $cuisines = [];
        }
        $cuisineParam = (!$dropCuisine && count($cuisines)) ? implode(',', $cuisines) : null;

        $rng = $wider ? [0.7, 1.35] : [0.9, 1.1];
        $minK = (int)floor($targetKcal * $rng[0]);
        $maxK = (int)ceil($targetKcal * $rng[1]);

        $diet = $this->baseDietFromOptions($options);

        $query = [
            'diet' => $diet,
            'cuisine' => $cuisineParam,
            'type' => $apiType,
            'number' => 4,
            'instructionsRequired' => true,
            'addRecipeNutrition' => true,
        ];

        if (!$wider) {
            $query['maxReadyTime'] = $this->maxReadyTimeFor($options['difficulty'] ?? 'Normal');
        }

        $resp = Http::withHeaders(['x-api-key' => config('services.spoonacular.key')])
            ->get('https://api.spoonacular.com/recipes/complexSearch', array_filter($query, fn($v) => $v !== null));

        $data = $resp->json();
        if (empty($data['results'])) {
            return null;
        }

        $sorted = $data['results'];
        usort($sorted, function ($a, $b) use ($targetKcal) {
            $ca = $this->extractCalories($a) ?? 1_000_000_000;
            $cb = $this->extractCalories($b) ?? 1_000_000_000;
            return abs($ca - $targetKcal) <=> abs($cb - $targetKcal);
        });

        foreach ($sorted as $r) {
            $id = (int)($r['id'] ?? 0);
            if (!$id) {
                continue;
            }

            if (isset($this->recipeLastUsedDay[$id]) && ($day - $this->recipeLastUsedDay[$id]) < 7) {
                continue;
            }

            if (!$wider && in_array($id, $this->usedRecipeIds, true)) {
                continue;
            }

            $info = $this->getRecipeInformation($id);
            if (!$info) {
                continue;
            }

            $meal = array_merge($r, $info);

            $ready = (int)($meal['readyInMinutes'] ?? 999);
            if (!$wider && !$this->checkDifficulty($ready, $options['difficulty'] ?? 'Normal')) {
                continue;
            }

            $this->usedRecipeIds[] = $id;
            $this->recipeLastUsedDay[$id] = $day;

            $meal['diet'] = $diet;
            $meal['diet_type'] = $this->resolveDietType($meal, $options);
            if (!isset($meal['cuisine'])) {
                $meal['cuisine'] = isset($meal['cuisines']) && is_array($meal['cuisines']) && count($meal['cuisines'])
                    ? implode(',', $meal['cuisines'])
                    : null;
            }

            return $meal;
        }

        return null;
    }

    private function fetchMealFromDatabase(
        array  $options,
        string $userType,
        int    $targetKcal,
        bool   $wider,
        bool   $dropCuisine,
        int    $day
    ): ?array {
        $diet = $this->baseDietFromOptions($options);
        $cuisines = $options['cuisines'] ?? [];
        if (!is_array($cuisines)) {
            $cuisines = [];
        }

        $query = Meal::query()
            ->where('type', strtolower($userType));

        if ($targetKcal > 0) {
            $rng = $wider ? [0.7, 1.35] : [0.9, 1.1];
            $minK = (int)floor($targetKcal * $rng[0]);
            $maxK = (int)ceil($targetKcal * $rng[1]);
            $query->whereBetween('calories', [$minK, $maxK]);
        }

        if ($diet) {
            $query->where(function ($q) use ($diet) {
                $q->where('diet', $diet)->orWhere('diet_type', $diet);
            });
        }

        if (!$dropCuisine && count($cuisines)) {
            $query->where(function ($q) use ($cuisines) {
                foreach ($cuisines as $cuisine) {
                    $q->orWhere('cuisine', 'LIKE', '%' . $cuisine . '%');
                }
            });
        }

        if (!$wider) {
            $query->where('ready_in_minutes', '<=', $this->maxReadyTimeFor($options['difficulty'] ?? 'Normal'));
        }

        $meals = $query->get()->all();
        if (!count($meals)) {
            return null;
        }

        usort($meals, function (Meal $a, Meal $b) use ($targetKcal) {
            return abs($a->calories - $targetKcal) <=> abs($b->calories - $targetKcal);
        });

        foreach ($meals as $meal) {
            $localKey = 'local_' . $meal->id;

            if (isset($this->recipeLastUsedDay[$localKey]) && ($day - $this->recipeLastUsedDay[$localKey]) < 7) {
                continue;
            }

            if (!$wider && in_array($localKey, $this->usedRecipeIds, true)) {
                continue;
            }

            $full = $this->buildMealArrayFromDatabase($meal);

            $this->usedRecipeIds[] = $localKey;
            $this->recipeLastUsedDay[$localKey] = $day;

            return $full;
        }

        return null;
    }

    private function fetchFallbackMeal(
        array  $options,
        string $userType,
        string $apiType,
        int    $targetKcal,
        int    $day
    ): ?array {
        $local = $this->fetchMealFromDatabase($options, $userType, $targetKcal, true, true, $day);
        if ($local) {
            return $local;
        }

        $diet = $this->baseDietFromOptions($options);
        $cuisines = $options['cuisines'] ?? [];
        if (!is_array($cuisines)) {
            $cuisines = [];
        }
        $cuisineParam = count($cuisines) ? implode(',', $cuisines) : null;

        $query = [
            'diet' => $diet,
            'cuisine' => $cuisineParam,
            'type' => $apiType,
            'number' => 4,
            'instructionsRequired' => true,
            'addRecipeNutrition' => true,
        ];

        $resp = Http::withHeaders(['x-api-key' => config('services.spoonacular.key')])
            ->get('https://api.spoonacular.com/recipes/complexSearch', array_filter($query, fn($v) => $v !== null));

        $data = $resp->json();
        if (empty($data['results'])) {
            return null;
        }

        $results = $data['results'];
        usort($results, function ($a, $b) use ($targetKcal) {
            $ca = $this->extractCalories($a) ?? 1_000_000_000;
            $cb = $this->extractCalories($b) ?? 1_000_000_000;
            return abs($ca - $targetKcal) <=> abs($cb - $targetKcal);
        });

        foreach ($results as $r) {
            $id = (int)($r['id'] ?? 0);
            if (!$id) {
                continue;
            }

            if (isset($this->recipeLastUsedDay[$id]) && ($day - $this->recipeLastUsedDay[$id]) < 7) {
                continue;
            }

            if (in_array($id, $this->usedRecipeIds, true)) {
                continue;
            }

            $info = $this->getRecipeInformation($id);
            if (!$info) {
                continue;
            }

            $meal = array_merge($r, $info);

            $this->usedRecipeIds[] = $id;
            $this->recipeLastUsedDay[$id] = $day;

            $meal['diet'] = $diet;
            $meal['diet_type'] = $this->resolveDietType($meal, $options);
            if (!isset($meal['cuisine'])) {
                $meal['cuisine'] = isset($meal['cuisines']) && is_array($meal['cuisines']) && count($meal['cuisines'])
                    ? implode(',', $meal['cuisines'])
                    : null;
            }

            return $meal;
        }

        return null;
    }

    private function buildMealArrayFromDatabase(Meal $meal): array
    {
        $ingredientsData = [];
        $mealIngredients = MealIngredient::where('meal_id', $meal->id)->get();

        foreach ($mealIngredients as $pivot) {
            $ingredient = Ingredient::find($pivot->ingredient_id);
            if (!$ingredient) {
                continue;
            }

            $meta = $pivot->meta ? json_decode($pivot->meta, true) : [];
            $amountMetric = $pivot->amount_metric ?? $pivot->amount ?? 0;
            $unitMetric = $pivot->unit_metric ?? $pivot->unit ?? '';

            $ingredientsData[] = [
                'id' => $ingredient->spoonacular_id ?? $ingredient->id,
                'aisle' => $ingredient->aisle,
                'image' => $ingredient->image,
                'consistency' => null,
                'name' => $ingredient->name,
                'nameClean' => $ingredient->name,
                'original' => $pivot->original,
                'originalName' => $pivot->original,
                'amount' => (float)$amountMetric,
                'unit' => $unitMetric,
                'meta' => $meta,
                'measures' => [
                    'metric' => [
                        'amount' => (float)$amountMetric,
                        'unitShort' => $unitMetric,
                        'unitLong' => $unitMetric,
                    ],
                ],
            ];
        }

        return [
            'id' => $meal->spoonacular_id ?: $meal->id,
            'title' => $meal->title,
            'readyInMinutes' => $meal->ready_in_minutes,
            'image' => $meal->image,
            'instructions' => $meal->instructions,
            'nutrition' => [
                'nutrients' => [
                    [
                        'name' => 'Calories',
                        'amount' => $meal->calories,
                    ],
                ],
            ],
            'calories' => $meal->calories,
            'extendedIngredients' => $ingredientsData,
            'diet' => $meal->diet,
            'diet_type' => $meal->diet_type,
            'cuisines' => $meal->cuisine
                ? array_filter(array_map('trim', explode(',', $meal->cuisine)))
                : [],
            'cuisine' => $meal->cuisine,
        ];
    }

    private function maxReadyTimeFor(string $level): int
    {
        return match ($level) {
            'Easy' => 25,
            'Normal' => 45,
            default => 120,
        };
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

    public function storePlanToDatabase(array $generatedPlan, array $options, int $userId): MealPlan
    {
        $mealPlan = MealPlan::create([
            'user_id' => $userId,
            'title' => $options['name'] ?? 'Custom Meal Plan',
            'total_days' => (int)$options['duration'],
            'daily_calories' => (int)$options['calories'],
            'daily_meals' => (int)($options['meals'] ?? $options['meal'] ?? 3),
            'diet_type' => $options['diet'] ?? null,
            'plan_difficulty' => $options['difficulty'] ?? null,
            'cuisines' => $options['cuisines'] ?? [],
        ]);

        foreach ($generatedPlan as $dayData) {
            $day = $mealPlan->mealPlanDay()->create([
                'day_number' => (int)$dayData['day'],
                'total_calories' => (int)collect($dayData['meal'])->sum('calories'),
            ]);

            foreach ($dayData['meal'] as $index => $mealData) {
                $diet = $mealData['diet'] ?? $this->baseDietFromOptions($options);
                $dietType = $this->resolveDietType($mealData, $options);
                $cuisine = $mealData['cuisine'] ?? (
                isset($mealData['cuisines']) && is_array($mealData['cuisines']) && count($mealData['cuisines'])
                    ? implode(',', $mealData['cuisines'])
                    : null
                );

                $meal = Meal::firstOrCreate(
                    ['spoonacular_id' => (int)$mealData['id']],
                    [
                        'title' => $mealData['title'],
                        'type' => strtolower($mealData['type']),
                        'ready_in_minutes' => (int)($mealData['readyInMinutes'] ?? 0),
                        'calories' => (int)($mealData['calories'] ?? $this->extractCalories($mealData) ?? 0),
                        'diet' => $diet,
                        'diet_type' => $dietType,
                        'cuisine' => $cuisine,
                        'instructions' => (string)($mealData['instructions'] ?? ''),
                        'image' => $mealData['image'] ?? null,
                    ]
                );

                foreach ($mealData['ingredients'] as $ingredientData) {
                    $ingredient = Ingredient::firstOrCreate(
                        ['spoonacular_id' => (int)$ingredientData['id']],
                        [
                            'name' => $ingredientData['name'] ?? ($ingredientData['nameClean'] ?? ''),
                            'image' => $ingredientData['image'] ?? null,
                            'aisle' => $ingredientData['aisle'] ?? '',
                        ]
                    );

                    $amountMetric = isset($ingredientData['measures']['metric']['amount'])
                        ? (float)$ingredientData['measures']['metric']['amount']
                        : null;
                    $unitMetric = $ingredientData['measures']['metric']['unitShort'] ?? null;

                    if ($amountMetric === null || $amountMetric === 0.0) {
                        $amountMetric = (float)($ingredientData['amount'] ?? 0);
                    }
                    if (!$unitMetric || strtolower($unitMetric) === 'servings') {
                        $unitMetric = ($ingredientData['consistency'] ?? '') === 'LIQUID' ? 'ml' : 'g';
                    }

                    $original = $ingredientData['original'] ?? ($ingredientData['name'] ?? '');
                    $metaJson = json_encode($ingredientData['meta'] ?? []);

                    MealIngredient::firstOrCreate([
                        'meal_id' => $meal->id,
                        'ingredient_id' => $ingredient->id,
                        'amount' => (float)($ingredientData['amount'] ?? 0),
                        'unit' => $ingredientData['unit'] ?? '-',
                        'amount_metric' => (float)$amountMetric,
                        'unit_metric' => $unitMetric,
                        'original' => $original,
                        'meta' => $metaJson,
                    ]);

                    $item = $day->shoppingListItems()->firstOrCreate(
                        [
                            'meal_plan_day_id' => $day->id,
                            'ingredient_id' => $ingredient->id,
                            'unit' => $unitMetric,
                        ],
                        [
                            'total_amount' => 0,
                            'meta' => $metaJson,
                        ]
                    );

                    $item->increment('total_amount', (float)round($amountMetric, 2));
                }

                $day->mealPlanDayMeal()->create([
                    'meal_id' => $meal->id,
                    'meal_type' => $mealData['type'],
                    'position' => $index + 1,
                ]);
            }
        }

        return $mealPlan;
    }

    private function checkDifficulty(int $minutes, string $level): bool
    {
        return match ($level) {
            'Easy' => $minutes <= 25,
            'Normal' => $minutes <= 45,
            default => true,
        };
    }

    private function extractCalories(array $meal): ?int
    {
        if (isset($meal['nutrition']['nutrients'])) {
            foreach ($meal['nutrition']['nutrients'] as $nutrient) {
                if (strtolower($nutrient['name']) === 'calories') {
                    return (int)round($nutrient['amount']);
                }
            }
        }

        if (isset($meal['calories'])) {
            return (int)round($meal['calories']);
        }

        return null;
    }

    private function baseDietFromOptions(array $options): ?string
    {
        return (($options['diet'] ?? 'None') !== 'None') ? $options['diet'] : null;
    }

    private function resolveDietType(array $meal, array $options): ?string
    {
        if (!empty($meal['diet_type'])) {
            return $meal['diet_type'];
        }

        if (isset($meal['diets']) && is_array($meal['diets']) && count($meal['diets'])) {
            return implode(',', $meal['diets']);
        }

        $diet = $this->baseDietFromOptions($options);
        if ($diet) {
            return $diet;
        }

        return null;
    }

    private function allowedMealTypes(array $options): array
    {
        $count = (int)($options['meals'] ?? $options['meal'] ?? 3);
        $count = max(1, min(6, $count));

        $templates = [
            1 => ['dinner'],
            2 => ['lunch', 'dinner'],
            3 => ['breakfast', 'lunch', 'dinner'],
            4 => ['breakfast', 'lunch', 'afternoon snack', 'dinner'],
            5 => ['breakfast', 'afternoon snack', 'lunch', 'evening snack', 'dinner'],
            6 => ['breakfast', 'second breakfast', 'lunch', 'afternoon snack', 'dinner', 'evening snack'],
        ];

        $map = [
            'breakfast' => 'breakfast',
            'second breakfast' => 'snack',
            'lunch' => 'main course',
            'afternoon snack' => 'snack',
            'dinner' => 'main course',
            'evening snack' => 'snack',
        ];

        $picked = $templates[$count];

        return array_map(fn($t) => [$t, $map[$t]], $picked);
    }

    private function caloriesDistribution(int $total, int $meals): array
    {
        $weights = [
            1 => [1.00],
            2 => [0.45, 0.55],
            3 => [0.25, 0.35, 0.40],
            4 => [0.25, 0.35, 0.10, 0.30],
            5 => [0.20, 0.10, 0.35, 0.10, 0.25],
            6 => [0.18, 0.10, 0.30, 0.10, 0.22, 0.10],
        ][$meals];

        $out = [];
        foreach ($weights as $w) {
            $out[] = (int)round($total * $w);
        }

        return $out;
    }
}
