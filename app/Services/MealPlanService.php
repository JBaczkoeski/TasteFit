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
    public function generateCustomPlan(array $options): array
    {
        $plan = [];
        $pairs = $this->allowedMealTypes($options);
        $userTypes = array_map(fn($p) => $p[0], $pairs);
        $apiTypes  = array_map(fn($p) => $p[1], $pairs);
        $targets = $this->caloriesDistribution((int)$options['calories'], count($pairs));

        for ($day = 1; $day <= (int)$options['duration']; $day++) {
            $dayMeals = [];
            for ($i=0; $i<count($pairs); $i++) {
                [$userType,$apiType] = $pairs[$i];
                $kcalTarget = $targets[$i];
                $meal = $this->fetchMeal($options,$apiType,$kcalTarget,false,false,false);
                if (!$meal) $meal = $this->fetchMeal($options,$apiType,$kcalTarget,true,false,false);
                if (!$meal) $meal = $this->fetchMeal($options,$apiType,$kcalTarget,true,true,false);
                if (!$meal) $meal = $this->fetchMeal($options,$apiType,$kcalTarget,true,true,true);
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
                    ];
                }
            }
            $need = (int)$options['calories'] - (int)collect($dayMeals)->sum('calories');
            if ($need > (int)round($options['calories']*0.08)) {
                $booster = $this->fetchMeal($options,'snack',$need,true,true,true);
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
                    ];
                }
            }
            $plan[] = ['day' => $day, 'meal' => array_slice($dayMeals,0,count($pairs))];
        }
        return $plan;
    }

    private function fetchMeal(array $options, string $apiType, int $targetKcal, bool $wider, bool $dropDiet, bool $dropCuisine): ?array
    {
        $cuisines = $options['cuisines'] ?? [];
        if (!is_array($cuisines)) $cuisines = [];
        $cuisineParam = (!$dropCuisine && count($cuisines)) ? implode(',', $cuisines) : null;

        $rng = $wider ? [0.7, 1.35] : [0.9, 1.1];
        $minK = (int)floor($targetKcal*$rng[0]);
        $maxK = (int)ceil($targetKcal*$rng[1]);

        $query = [
            'diet' => (!$dropDiet && (($options['diet'] ?? 'None') !== 'None')) ? $options['diet'] : null,
            'cuisine' => $cuisineParam,
            'type' => $apiType,
            'number' => 10,
            'instructionsRequired' => true,
            'addRecipeNutrition' => true,
            'minCalories' => $minK,
            'maxCalories' => $maxK,
        ];

        if (!$wider) $query['maxReadyTime'] = $this->maxReadyTimeFor($options['difficulty'] ?? 'Normal');

        $resp = Http::withHeaders(['x-api-key' => config('services.spoonacular.key')])
            ->get('https://api.spoonacular.com/recipes/complexSearch', array_filter($query, fn($v)=>$v!==null));

        $data = $resp->json();
        if (empty($data['results'])) return null;

        $sorted = $data['results'];
        usort($sorted, function($a,$b) use($targetKcal){
            $ca = $this->extractCalories(['nutrition'=>$a['nutrition']??[]]) ?? 1e9;
            $cb = $this->extractCalories(['nutrition'=>$b['nutrition']??[]]) ?? 1e9;
            return abs($ca-$targetKcal) <=> abs($cb-$targetKcal);
        });

        foreach ($sorted as $r) {
            $id = (int)$r['id'];
            if (!$wider && in_array($id,$this->usedRecipeIds,true)) continue;
            $info = $this->getRecipeInformation($id);
            if (!$info) continue;
            if (!$this->checkDifficulty((int)($info['readyInMinutes'] ?? 999), $options['difficulty'] ?? 'Normal') && !$wider) continue;
            $this->usedRecipeIds[] = $id;
            return array_merge($r,$info);
        }
        return null;
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

    public function storePlanToDatabase(array $generatedPlan, array $options, int $userId): \App\Models\MealPlan
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
                $meal = Meal::firstOrCreate(
                    ['spoonacular_id' => (int)$mealData['id']],
                    [
                        'title' => $mealData['title'],
                        'type' => strtolower($mealData['type']),
                        'ready_in_minutes' => (int)($mealData['readyInMinutes'] ?? 0),
                        'calories' => (int)($mealData['calories'] ?? 0),
                        'instructions' => (string)($mealData['instructions'] ?? ''),
                        'image' => $mealData['image'] ?? null,
                    ]
                );

                foreach ($mealData['ingredients'] as $ingredientData) {
                    $ingredient = Ingredient::firstOrCreate(
                        ['spoonacular_id' => (int)$ingredientData['id']],
                        [
                            'name'  => $ingredientData['name'] ?? ($ingredientData['nameClean'] ?? ''),
                            'image' => $ingredientData['image'] ?? null,
                            'aisle' => $ingredientData['aisle'] ?? '',
                        ]
                    );

                    $amountMetric = isset($ingredientData['measures']['metric']['amount'])
                        ? (float) $ingredientData['measures']['metric']['amount']
                        : null;
                    $unitMetric = $ingredientData['measures']['metric']['unitShort'] ?? null;

                    if ($amountMetric === null || $amountMetric === 0.0) {
                        $amountMetric = (float) ($ingredientData['amount'] ?? 0); // ostateczny fallback
                    }
                    if (!$unitMetric || strtolower($unitMetric) === 'servings') {
                        $unitMetric = $ingredientData['consistency'] === 'LIQUID' ? 'ml' : 'g';
                    }

                    $original = $ingredientData['original'] ?? ($ingredientData['name'] ?? '');
                    $metaJson = json_encode($ingredientData['meta'] ?? []);

                    MealIngredient::firstOrCreate([
                        'meal_id'        => $meal->id,
                        'ingredient_id'  => $ingredient->id,
                        'amount'         => (float)($ingredientData['amount'] ?? 0),
                        'unit'           => $ingredientData['unit'] ?? '-',
                        'amount_metric'  => (float)$amountMetric,
                        'unit_metric'    => $unitMetric,
                        'original'       => $original,
                        'meta'           => $metaJson,
                    ]);

                    $item = $day->shoppingListItems()->firstOrCreate(
                        [
                            'meal_plan_day_id' => $day->id,
                            'ingredient_id'    => $ingredient->id,
                            'unit'             => $unitMetric,   // <— TYLKO METRYKA
                        ],
                        [
                            'total_amount' => 0,
                            'meta'         => $metaJson,
                        ]
                    );

                    $item->increment('total_amount', (float) round($amountMetric, 2));
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

    private function allowedMealTypes(array $options): array
    {
        $count = (int)($options['meals'] ?? $options['meal'] ?? 3);
        $count = max(1, min(6, $count));
        $templates = [
            1 => ['dinner'],
            2 => ['lunch','dinner'],
            3 => ['breakfast','lunch','dinner'],
            4 => ['breakfast','lunch','afternoon snack','dinner'],
            5 => ['breakfast','afternoon snack','lunch','evening snack','dinner'],
            6 => ['breakfast','second breakfast','lunch','afternoon snack','dinner','evening snack'],
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
            2 => [0.45,0.55],
            3 => [0.25,0.35,0.40],
            4 => [0.25,0.35,0.10,0.30],
            5 => [0.20,0.10,0.35,0.10,0.25],
            6 => [0.18,0.10,0.30,0.10,0.22,0.10],
        ][$meals];
        $out=[]; foreach ($weights as $w) $out[]=(int)round($total*$w);
        return $out;
    }
}
