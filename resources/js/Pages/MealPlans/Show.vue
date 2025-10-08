<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ plan: Object })

const statusClass = computed(() =>
    props.plan.status === 'active'
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-600'
)

const tab = ref('days')

const mealTypeOrder = { breakfast: 1, snack: 2, lunch: 3, dinner: 4, supper: 5, other: 99 }
const sortedDays = computed(() =>
    [...(props.plan.meal_plan_day || props.plan.mealPlanDay || [])].sort((a,b)=>a.day_number-b.day_number)
)

function groupMeals(day) {
    const groups = {}
    ;(day.meal_plan_day_meal || day.mealPlanDayMeal || []).forEach(m => {
        const t = (m.meal_type || 'other').toLowerCase()
        if (!groups[t]) groups[t] = []
        groups[t].push(m)
    })
    return Object.entries(groups).sort((a,b) => (mealTypeOrder[a[0]]||99) - (mealTypeOrder[b[0]]||99))
}

const shopping = computed(() => {
    const items = {}
    sortedDays.value.forEach(d => {
        (d.shopping_list_items || d.shoppingListItems || []).forEach(it => {
            const key = (it.ingredient_name || it.name || '').toLowerCase()
            if (!key) return
            if (!items[key]) items[key] = { name: it.ingredient_name || it.name, amount: 0, unit: it.unit || '' }
            items[key].amount += Number(it.amount || 0)
            if (!items[key].unit && it.unit) items[key].unit = it.unit
        })
    })
    return Object.values(items).sort((a,b)=>a.name.localeCompare(b.name))
})

const stripTags = s => (s ?? '').replace(/<[^>]*>/g, '');
</script>

<template>
    <Head :title="`Meal Plan: ${plan.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ plan.title }}</h2>
                    <p class="text-gray-500">Created: {{ plan.created_at_human || plan.created_at }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass">{{ plan.status }}</span>
                    <Link :href="route('plan.meal.create')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">+ Create New Plan</Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6 border-b grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Days</div>
                            <div class="text-xl font-semibold">{{ plan.total_days }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Daily Calories</div>
                            <div class="text-xl font-semibold">{{ plan.daily_calories }} kcal</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Meals per Day</div>
                            <div class="text-xl font-semibold">{{ plan.daily_meals }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Diet</div>
                            <div class="text-xl font-semibold">{{ plan.diet_type }}</div>
                        </div>
                    </div>

                    <div class="px-6 pt-4">
                        <div class="inline-flex rounded-lg border bg-gray-50 p-1">
                            <button class="px-4 py-2 rounded-md text-sm"
                                    :class="tab==='days' ? 'bg-white shadow font-semibold' : 'text-gray-600'"
                                    @click="tab='days'">Days</button>
                            <button class="px-4 py-2 rounded-md text-sm"
                                    :class="tab==='shopping' ? 'bg-white shadow font-semibold' : 'text-gray-600'"
                                    @click="tab='shopping'">Shopping List</button>
                        </div>
                    </div>

                    <div v-if="tab==='days'" class="p-6 space-y-8">
                        <div v-for="day in sortedDays" :key="day.id" class="border rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                                <div class="font-semibold">Day {{ day.day_number }}</div>
                                <div class="text-sm text-gray-600">Target: {{ day.total_calories }} kcal</div>
                            </div>

                            <div class="p-4 space-y-6">
                                <div v-for="[type, meals] in groupMeals(day)" :key="type" class="space-y-3">
                                    <div class="text-sm uppercase tracking-wide text-gray-500">{{ type }}</div>
                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div v-for="m in meals" :key="m.id" class="border rounded-lg">
                                            <div class="p-4 flex gap-4">
                                                <img v-if="m.meal?.image_url" :src="m.meal.image_url" alt="" class="w-20 h-20 rounded object-cover">
                                                <div class="min-w-0">
                                                    <div class="font-semibold truncate">{{ m.meal?.title || 'Meal' }}</div>
                                                    <div class="text-xs text-gray-600">
                                                        <span v-if="m.meal?.ready_in_minutes">{{ m.meal.ready_in_minutes }} min</span>
                                                        <span v-if="m.meal?.servings"> • {{ m.meal.servings }} servings</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 pb-4">
                                                <div class="text-xs uppercase text-gray-500 mb-1">Ingredients</div>
                                                <ul class="text-sm text-gray-700 list-disc ml-5 space-y-0.5">
                                                    <li v-for="ing in (m.meal?.ingredients || [])" :key="ing.id">
                                                        {{ ing.ingredient.name }}
                                                        <span v-if="ing.amount_metric">– {{ Number(ing.amount_metric) }}{{ ing.unit_metric ? ' ' + ing.unit_metric : '' }}</span>
                                                    </li>
                                                    <li v-if="!m.meal || !m.meal.ingredients || m.meal.ingredients.length===0" class="text-gray-400">No ingredients</li>
                                                </ul>
                                                <div class="mt-3">
                                                    <a v-if="m.meal?.source_url" :href="m.meal.source_url" target="_blank" class="text-blue-600 text-sm hover:underline">Open recipe</a>
                                                </div>
                                            </div>
                                            <div class="px-4 pb-4">
                                                <div class="text-xs uppercase text-gray-500 mb-1">Instruction</div>
                                                <p class="whitespace-pre-line">{{ stripTags(m.meal?.instructions) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

<!--                                <div v-if="(day.shopping_list_items || day.shoppingListItems || []).length" class="pt-2">-->
<!--                                    <div class="text-xs uppercase tracking-wide text-gray-500 mb-1">Day Shopping</div>-->
<!--                                    <ul class="text-sm text-gray-700 list-disc ml-5">-->
<!--                                        <li v-for="it in (day.shopping_list_items || day.shoppingListItems)" :key="it.id">-->
<!--                                            {{ it.ingredient_name || it.name }} – {{ Number(it.amount || 0) }}{{ it.unit ? ' ' + it.unit : '' }}-->
<!--                                        </li>-->
<!--                                    </ul>-->
<!--                                </div>-->
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-6">
                        <div v-if="shopping.length" class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600 border-b">
                                <tr>
                                    <th class="px-4 py-3">Ingredient</th>
                                    <th class="px-4 py-3">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in shopping" :key="row.name" class="border-b">
                                    <td class="px-4 py-3">{{ row.name }}</td>
                                    <td class="px-4 py-3">{{ row.amount }}{{ row.unit ? ' ' + row.unit : '' }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-gray-500">No shopping items.</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
