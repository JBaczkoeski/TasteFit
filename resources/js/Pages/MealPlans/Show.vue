<script setup>
import {Head, Link} from '@inertiajs/vue3'
import {computed, ref} from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({plan: Object})

const tab = ref('days')
const expanded = ref({})

const statusClass = computed(() =>
    props.plan.status === 'active'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-zinc-100 text-zinc-700'
)

const mealTypeOrder = {
    breakfast: 1,
    'second breakfast': 2,
    'afternoon snack': 3,
    snack: 3,
    lunch: 4,
    dinner: 5,
    'evening snack': 6,
    supper: 6,
    other: 99
}
const sortedDays = computed(() =>
    [...(props.plan.meal_plan_day || props.plan.mealPlanDay || [])].sort((a, b) => a.day_number - b.day_number)
)

function mealsFlat(day) {
    const list = [...(day.meal_plan_day_meal || day.mealPlanDayMeal || [])]
    return list.sort((a, b) => {
        const ta = (a.meal_type || 'other').toLowerCase()
        const tb = (b.meal_type || 'other').toLowerCase()
        const oa = mealTypeOrder[ta] ?? 99
        const ob = mealTypeOrder[tb] ?? 99
        if (oa !== ob) return oa - ob
        return (a.position || 0) - (b.position || 0)
    })
}

const shopping = computed(() => {
    const items = {}
    sortedDays.value.forEach(d => {
        (d.shopping_list_items || d.shoppingListItems || []).forEach(it => {
            const key = (it.ingredient_name || it.name || '').toLowerCase() + '|' + (it.unit || '')
            if (!key.trim()) return
            if (!items[key]) items[key] = {name: it.ingredient_name || it.name, amount: 0, unit: it.unit || ''}
            items[key].amount += Number(it.amount || 0)
        })
    })
    return Object.values(items).sort((a, b) => a.name.localeCompare(b.name))
})

function perDayShopping(day) {
    const map = {};
    (day.shopping_list_items || day.shoppingListItems || []).forEach(it => {
        const id = it.ingredient_id ?? it.ingredient?.id ?? null;
        const unit = it.unit || '';
        if (!id) return;
        const key = `${id}|${unit}`;
        if (!map[key]) {
            map[key] = {
                id,
                name: it.ingredient_name || it.name || it.ingredient?.name || '—',
                unit,
                amount: 0,
            };
        }
        map[key].amount += Number(it.total_amount ?? it.amount ?? 0);
    });
    return Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
}

const fmt = (n) => {
    if (n == null) return '0';
    return Math.abs(n % 1) < 0.005 ? String(Math.round(n)) : n.toFixed(2);
};

const stripTags = s => (s ?? '').replace(/<[^>]*>/g, '')
const kcal = m => Number(m?.meal?.calories ?? m?.calories ?? 0)
const time = m => Number(m?.meal?.ready_in_minutes ?? m?.readyInMinutes ?? 0)
const img = m => m?.meal?.image_url || m?.meal?.image || m?.image || m?.meal?.imageUrl || 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1200&auto=format&fit=crop'
const servings = m => m?.meal?.servings
const mealTitle = m => m?.meal?.title || 'Meal'
const mealType = m => (m.meal_type || 'Other').toString()
const show = id => (expanded.value[id] = !expanded.value[id])
</script>

<template>
    <Head :title="`Meal Plan: ${plan.title}`"/>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-1">
                    <h2 class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ plan.title }}</h2>
                    <p class="text-sm text-zinc-500">Created: {{ plan.created_at_human || plan.created_at }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border border-transparent"
                          :class="statusClass">{{ plan.status }}</span>
                    <Link :href="route('plan.meal.create')"
                          class="px-4 py-2 rounded-xl text-sm font-semibold bg-zinc-900 text-white hover:bg-zinc-800">+
                        Create New Plan
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <!-- top stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 border-b border-zinc-200">
                        <div class="rounded-xl p-4 bg-zinc-50">
                            <div class="text-xs uppercase tracking-wider text-zinc-500">Days</div>
                            <div class="text-2xl font-bold text-zinc-900">{{ plan.total_days }}</div>
                        </div>
                        <div class="rounded-xl p-4 bg-zinc-50">
                            <div class="text-xs uppercase tracking-wider text-zinc-500">Daily Calories</div>
                            <div class="text-2xl font-bold text-zinc-900">{{ plan.daily_calories }} kcal</div>
                        </div>
                        <div class="rounded-xl p-4 bg-zinc-50">
                            <div class="text-xs uppercase tracking-wider text-zinc-500">Meals per Day</div>
                            <div class="text-2xl font-bold text-zinc-900">{{ plan.daily_meals }}</div>
                        </div>
                        <div class="rounded-xl p-4 bg-zinc-50">
                            <div class="text-xs uppercase tracking-wider text-zinc-500">Diet</div>
                            <div class="text-2xl font-bold text-zinc-900">{{ plan.diet_type }}</div>
                        </div>
                    </div>

                    <!-- tabs -->
                    <div class="px-6 pt-4">
                        <div class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 p-1">
                            <button class="px-4 py-2 rounded-full text-sm transition"
                                    :class="tab==='days' ? 'bg-white shadow-sm font-semibold' : 'text-zinc-600'"
                                    @click="tab='days'">Days
                            </button>
                            <button class="px-4 py-2 rounded-full text-sm transition"
                                    :class="tab==='shopping' ? 'bg-white shadow-sm font-semibold' : 'text-zinc-600'"
                                    @click="tab='shopping'">Shopping List
                            </button>
                        </div>
                    </div>

                    <!-- days -->
                    <div v-if="tab==='days'" class="p-6 space-y-8">
                        <div v-for="day in sortedDays" :key="day.id"
                             class="rounded-2xl overflow-hidden ring-1 ring-zinc-200">
                            <div
                                class="bg-gradient-to-r from-zinc-50 to-white px-5 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-xl bg-zinc-900 text-white grid place-items-center text-sm font-bold">
                                        D{{ day.day_number }}
                                    </div>
                                    <div class="text-lg font-semibold text-zinc-900">Day {{ day.day_number }}</div>
                                </div>
                                <div class="text-sm text-zinc-700">Target: <span
                                    class="font-semibold">{{ day.total_calories }}</span> kcal
                                </div>
                            </div>

                            <!-- grid: dokładnie 3 karty w rzędzie na desktopie -->
                            <div class="p-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    <div
                                        v-for="m in mealsFlat(day)"
                                        :key="m.id"
                                        class="flex flex-col h-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm hover:shadow-md transition"
                                    >
                                        <!-- header (stała wysokość obrazu) -->
                                        <div class="relative">
                                            <img :src="img(m)" alt="" class="h-44 w-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>

                                            <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                                <span
                                                    class="backdrop-blur px-2 py-1 rounded-full text-[11px] font-semibold text-white/95 bg-black/35">{{
                                                        mealType(m)
                                                    }}</span>
                                            </div>
                                            <div class="absolute top-3 right-3 flex gap-2">
                                                <span
                                                    class="backdrop-blur px-2 py-1 rounded-full text-[11px] font-semibold text-white/95 bg-black/35">⏱ {{
                                                        time(m) || '—'
                                                    }} min</span>
                                                <span
                                                    class="backdrop-blur px-2 py-1 rounded-full text-[11px] font-semibold text-white/95 bg-black/35">🔥 {{
                                                        kcal(m) || '—'
                                                    }} kcal</span>
                                            </div>

                                            <div class="absolute bottom-3 left-3 right-3">
                                                <div class="line-clamp-2 text-white font-semibold drop-shadow">
                                                    {{ mealTitle(m) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- body (rośnie) -->
                                        <div class="p-4 space-y-3 flex-1">
                                            <div class="text-xs uppercase text-zinc-500">Ingredients</div>
                                            <ul class="text-sm text-zinc-800 space-y-1">
                                                <li v-for="ing in (m.meal?.ingredients || [])" :key="ing.id"
                                                    class="flex items-center justify-between">
                                                    <span class="truncate">{{ ing.ingredient?.name || ing.name }}</span>
                                                    <span class="text-zinc-500 ml-3 shrink-0">
                            <span v-if="Number(ing.amount_metric)">{{ Number(ing.amount_metric) }}</span>
                            <span v-if="ing.unit_metric"> {{ ing.unit_metric }}</span>
                          </span>
                                                </li>
                                                <li v-if="!m.meal || !m.meal.ingredients || m.meal.ingredients.length===0"
                                                    class="text-zinc-400">No ingredients
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- footer (stały układ przycisków) -->
                                        <div class="px-4 pb-4 pt-2 mt-auto flex flex-wrap items-center gap-3">
                                            <button
                                                @click="show(m.id)"
                                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
                                            >
                                                <span>Show instructions</span>
                                            </button>
                                            <a
                                                v-if="m.meal?.source_url"
                                                :href="m.meal.source_url"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                                            >
                                                Open recipe
                                            </a>
                                        </div>

                                        <!-- expandable instructions -->
                                        <transition name="fade">
                                            <div v-if="expanded[m.id]"
                                                 class="mx-4 mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                                <p class="whitespace-pre-line">{{ stripTags(m.meal?.instructions) }}</p>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                            <div v-if="(day.shopping_list_items || day.shoppingListItems || []).length" class="px-5 pb-5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs uppercase tracking-wider text-zinc-500">Day Shopping</span>
                                    <span class="h-px flex-1 bg-zinc-200"></span>
                                </div>

                                <!-- UŻYJ perDayShopping(day), a nie surowej listy -->
                                <div class="mt-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div v-for="row in perDayShopping(day)" :key="row.id + row.unit"
                                         class="rounded-xl px-3 py-2 border border-zinc-200 bg-zinc-50 text-sm text-zinc-700">
                                        {{ row.name }} — {{ fmt(row.amount) }}<span v-if="row.unit"> {{ row.unit }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- shopping tab -->
                    <div v-else class="p-6">
                        <div v-if="shopping.length" class="overflow-x-auto rounded-2xl ring-1 ring-zinc-200">
                            <table class="min-w-full text-sm">
                                <thead
                                    class="bg-zinc-50 text-left text-xs uppercase tracking-wider text-zinc-600 border-b border-zinc-200">
                                <tr>
                                    <th class="px-4 py-3">Ingredient</th>
                                    <th class="px-4 py-3">Amount</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                <tr v-for="row in shopping" :key="row.name" class="bg-white">
                                    <td class="px-4 py-3 text-zinc-800">{{ row.name }}</td>
                                    <td class="px-4 py-3 text-zinc-800">
                                        {{ fmt(row.amount) }}<span v-if="row.unit"> {{ row.unit }}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-zinc-500">No shopping items.</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity .18s ease
}

.fade-enter-from, .fade-leave-to {
    opacity: 0
}
</style>
