<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    currentPlan: { type: Object, default: null },
    todayDayNumber: { type: Number, default: null },
    todayMeals: { type: Array, default: () => [] },
    todayShopping: { type: Array, default: () => [] },
    planImages: { type: Array, default: () => [] },
    history: { type: Array, default: () => [] },
})

const mealTypeOrder = {
    breakfast: 1,
    'second breakfast': 2,
    'afternoon snack': 3,
    snack: 3,
    lunch: 4,
    dinner: 5,
    'evening snack': 6,
    supper: 6,
    other: 99,
}

const sortedTodayMeals = computed(() => {
    const list = [...(props.todayMeals || [])]
    return list.sort((a, b) => {
        const ta = String(a.meal_type || 'other').toLowerCase()
        const tb = String(b.meal_type || 'other').toLowerCase()
        const oa = mealTypeOrder[ta] ?? 99
        const ob = mealTypeOrder[tb] ?? 99
        if (oa !== ob) return oa - ob
        return (Number(a.id) || 0) - (Number(b.id) || 0)
    })
})

const nowMinutes = () => {
    const d = new Date()
    return d.getHours() * 60 + d.getMinutes()
}

const mealWindowStart = (mealTypeRaw) => {
    const t = String(mealTypeRaw || 'other').toLowerCase().trim()
    if (t === 'breakfast') return 7 * 60
    if (t === 'second breakfast') return 10 * 60
    if (t === 'lunch') return 13 * 60
    if (t === 'afternoon snack' || t === 'snack') return 15 * 60
    if (t === 'dinner') return 19 * 60
    if (t === 'evening snack' || t === 'supper') return 21 * 60
    return 12 * 60
}

const nextMealByTime = computed(() => {
    const list = [...sortedTodayMeals.value]
        .map(m => ({ ...m, _start: mealWindowStart(m.meal_type) }))
        .sort((a, b) => a._start - b._start)

    if (!list.length) return null

    const current = nowMinutes()
    const upcoming = list.find(m => m._start >= current)
    if (upcoming) return { ...upcoming, _isTomorrow: false }

    return { ...list[0], _isTomorrow: true }
})

const nextMeal = nextMealByTime

const totalKcalToday = computed(() =>
    (props.todayMeals || []).reduce((sum, m) => sum + Number(m?.calories ?? 0), 0)
)

const totalCookMinutesToday = computed(() =>
    (props.todayMeals || []).reduce((sum, m) => sum + Number(m?.ready_in_minutes ?? 0), 0)
)

const kcalPct = computed(() => {
    const target = Number(props.currentPlan?.daily_calories ?? 0)
    if (!target) return 0
    return Math.min(100, Math.max(0, Math.round((totalKcalToday.value / Math.max(1, target)) * 100)))
})

const humanMinutes = (minutes) => {
    const total = Math.max(0, Number(minutes) || 0)
    const h = Math.floor(total / 60)
    const m = total % 60
    if (h <= 0) return `${m} min`
    return `${h}h ${String(m).padStart(2, '0')}m`
}

const timeHm = (mins) => {
    const h = Math.floor((mins || 0) / 60)
    const m = (mins || 0) % 60
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

const fmt = (n) => {
    if (n == null) return '0'
    return Math.abs(n % 1) < 0.005 ? String(Math.round(n)) : Number(n).toFixed(2)
}

const ingredientImg = (img) =>
    img ? `https://spoonacular.com/cdn/ingredients_100x100/${img}` : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=800&auto=format&fit=crop'

const mealImg = (img) =>
    img ? img : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1400&auto=format&fit=crop'

const statusBadge = computed(() => {
    const s = String(props.currentPlan?.status ?? '').toLowerCase()
    if (s === 'active') return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
    if (s) return 'bg-zinc-50 text-zinc-700 ring-zinc-200'
    return 'bg-zinc-50 text-zinc-600 ring-zinc-200'
})
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div class="space-y-1">
                    <div class="text-sm text-zinc-500">Dashboard</div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-zinc-900">
                        Welcome back, {{ $page.props.auth.user.name }}!
                    </h2>
                    <p class="text-sm text-zinc-500">
                        Day <span class="font-semibold text-zinc-800">{{ todayDayNumber ?? '—' }}</span>
                        <span v-if="currentPlan" class="ml-2">
                            • <span class="font-semibold text-zinc-800">{{ currentPlan.title }}</span>
                        </span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset" :class="statusBadge">
                        <span class="h-2 w-2 rounded-full" :class="currentPlan?.status==='active' ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                        {{ currentPlan?.status ?? 'no plan' }}
                    </span>

                    <Link v-if="currentPlan" :href="route('plan.meal.show', { mealPlan: currentPlan.id })"
                          class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                        Open plan
                    </Link>
                    <Link v-else :href="route('plan.meal.create')"
                          class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                        Create plan
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-zinc-200 bg-gradient-to-b from-white to-zinc-50/60 shadow-sm">
                    <div class="p-6 md:p-8 space-y-8">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                            <div class="lg:col-span-8 rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
                                <div class="relative">
                                    <img
                                        :src="mealImg(planImages?.[0] || nextMeal?.image || '')"
                                        class="h-44 w-full object-cover"
                                        alt=""
                                    />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>
                                    <div class="absolute bottom-4 left-5 right-5">
                                        <div class="text-white/90 text-xs font-semibold uppercase tracking-wider">
                                            Today snapshot
                                        </div>
                                        <div class="mt-1 text-white text-2xl font-extrabold tracking-tight line-clamp-2">
                                            {{ currentPlan ? `${currentPlan.daily_calories} kcal plan` : 'No active plan yet' }}
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                                Meals: {{ todayMeals.length }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                                Shopping: {{ todayShopping.length }}
                                            </span>
                                            <span v-if="currentPlan?.diet_type" class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                                                {{ currentPlan.diet_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                            <div class="text-xs uppercase tracking-wider text-zinc-500">Total kcal today</div>
                                            <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ totalKcalToday }} kcal</div>
                                            <div v-if="currentPlan" class="mt-3">
                                                <div class="h-2 w-full rounded-full bg-zinc-200 overflow-hidden">
                                                    <div class="h-2 rounded-full bg-emerald-500" :style="{ width: `${kcalPct}%` }"></div>
                                                </div>
                                                <div class="mt-2 text-xs text-zinc-500">
                                                    {{ kcalPct }}% of target ({{ currentPlan.daily_calories }} kcal)
                                                </div>
                                            </div>
                                            <div v-else class="mt-2 text-xs text-zinc-400">No target yet</div>
                                        </div>

                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                            <div class="text-xs uppercase tracking-wider text-zinc-500">Time to cook</div>
                                            <div class="mt-1 text-2xl font-extrabold text-zinc-900">{{ humanMinutes(totalCookMinutesToday) }}</div>
                                            <div class="mt-2 text-xs text-zinc-500" v-if="todayMeals.length">
                                                Avg: {{ humanMinutes(Math.round(totalCookMinutesToday / Math.max(1, todayMeals.length))) }}
                                            </div>
                                            <div class="mt-2 text-xs text-zinc-400" v-else>—</div>
                                        </div>

                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="text-xs uppercase tracking-wider text-zinc-500">Next meal</div>
                                                <span v-if="nextMeal" class="text-xs font-semibold"
                                                      :class="nextMeal._isTomorrow ? 'text-indigo-600' : 'text-emerald-700'">
                                                    {{ nextMeal._isTomorrow ? 'Tomorrow' : 'Today' }}
                                                </span>
                                            </div>

                                            <div v-if="nextMeal" class="mt-3 flex items-center gap-3">
                                                <img :src="mealImg(nextMeal.image)" class="h-12 w-12 rounded-xl object-cover" alt="">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-zinc-900 truncate">
                                                        {{ nextMeal.meal_type }}
                                                    </div>
                                                    <div class="text-sm text-zinc-600 truncate">
                                                        {{ nextMeal.title }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-zinc-500">
                                                        {{ nextMeal.calories }} kcal • {{ nextMeal.ready_in_minutes || '—' }} min • {{ timeHm(nextMeal._start) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-else class="mt-2 text-sm text-zinc-400">No meals</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-4 space-y-6">
                                <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm p-6">
                                    <div class="text-sm font-semibold text-zinc-900">Plan overview</div>
                                    <div class="mt-4 space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-500">Daily calories</span>
                                            <span class="font-semibold text-zinc-900">{{ currentPlan?.daily_calories ?? '—' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-500">Total days</span>
                                            <span class="font-semibold text-zinc-900">{{ currentPlan?.total_days ?? '—' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-500">Diet</span>
                                            <span class="font-semibold text-zinc-900">{{ currentPlan?.diet_type ?? '—' }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <div class="text-zinc-500">Cuisines</div>
                                            <div class="mt-1 text-zinc-900 font-semibold">
                                                <span v-if="currentPlan && currentPlan.cuisines && currentPlan.cuisines.length">
                                                    {{ currentPlan.cuisines.join(', ') }}
                                                </span>
                                                <span v-else class="text-zinc-400">—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="planImages.length" class="rounded-2xl border border-zinc-200 bg-white shadow-sm p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-zinc-900">Plan gallery</div>
                                        <div class="text-xs text-zinc-500">{{ planImages.length }}</div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-2">
                                        <img
                                            v-for="(img, idx) in planImages.slice(0, 9)"
                                            :key="idx"
                                            :src="mealImg(img)"
                                            class="h-20 w-full rounded-xl object-cover"
                                            alt=""
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                            <div class="lg:col-span-7 rounded-2xl border border-zinc-200 bg-white shadow-sm p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-zinc-900">Meals for Day {{ todayDayNumber ?? '—' }}</div>
                                        <div class="text-xs text-zinc-500 mt-1">Sorted by meal type and schedule</div>
                                    </div>
                                    <Link v-if="currentPlan" :href="route('plan.meal.show', { mealPlan: currentPlan.id })"
                                          class="text-sm font-semibold text-blue-600 hover:underline">
                                        Open plan →
                                    </Link>
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div
                                        v-for="m in sortedTodayMeals"
                                        :key="m.id"
                                        class="group rounded-2xl border border-zinc-200 overflow-hidden bg-white hover:shadow-md transition"
                                    >
                                        <div class="relative">
                                            <img :src="mealImg(m.image)" class="h-32 w-full object-cover" alt="">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/45 to-transparent"></div>
                                            <div class="absolute top-3 left-3 flex gap-2">
                                                <span class="rounded-full bg-black/35 text-white text-[11px] font-semibold px-2 py-1 backdrop-blur">
                                                    {{ m.meal_type }}
                                                </span>
                                                <span class="rounded-full bg-black/35 text-white text-[11px] font-semibold px-2 py-1 backdrop-blur">
                                                    {{ timeHm(mealWindowStart(m.meal_type)) }}
                                                </span>
                                            </div>
                                            <div class="absolute bottom-3 left-3 right-3 text-white font-semibold line-clamp-2">
                                                {{ m.title }}
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="font-semibold text-zinc-900">{{ m.calories }} kcal</span>
                                                <span class="text-zinc-500">{{ m.ready_in_minutes || '—' }} min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="!sortedTodayMeals.length" class="rounded-2xl border border-dashed border-zinc-300 p-6 text-zinc-500">
                                        No meals yet.
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-5 rounded-2xl border border-zinc-200 bg-white shadow-sm p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-zinc-900">Shopping list (Day {{ todayDayNumber ?? '—' }})</div>
                                        <div class="text-xs text-zinc-500 mt-1">Grouped by ingredient + unit</div>
                                    </div>
                                    <Link v-if="currentPlan" :href="route('shopping.list.index')"
                                          class="text-sm font-semibold text-blue-600 hover:underline">
                                        Open full list →
                                    </Link>
                                </div>

                                <div v-if="todayShopping.length" class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div
                                        v-for="row in todayShopping"
                                        :key="row.ingredient_id + '|' + row.unit"
                                        class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-3"
                                    >
                                        <img :src="ingredientImg(row.image)" class="h-11 w-11 rounded-xl object-cover" alt="">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-zinc-900 truncate">{{ row.name }}</div>
                                            <div class="text-sm text-zinc-600">
                                                {{ fmt(row.amount) }} <span v-if="row.unit">{{ row.unit }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="mt-5 rounded-2xl border border-dashed border-zinc-300 p-6 text-zinc-500">
                                    No shopping items for today.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
                            <div class="p-6 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-zinc-900">Plan history</div>
                                    <div class="text-xs text-zinc-500 mt-1">Latest plans</div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-zinc-50 text-left text-xs uppercase tracking-wider text-zinc-600 border-t border-b border-zinc-200">
                                    <tr>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">Title</th>
                                        <th class="px-6 py-3">Calories</th>
                                        <th class="px-6 py-3">Days</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                    <tr v-for="row in history" :key="row.id" class="bg-white hover:bg-zinc-50/70">
                                        <td class="px-6 py-4 text-zinc-800 whitespace-nowrap">{{ row.created_at }}</td>
                                        <td class="px-6 py-4 text-zinc-800">
                                            <div class="font-semibold line-clamp-1">{{ row.title || '—' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-zinc-800 whitespace-nowrap">{{ row.daily_calories }} kcal</td>
                                        <td class="px-6 py-4 text-zinc-800 whitespace-nowrap">{{ row.total_days }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset"
                                                  :class="String(row.status).toLowerCase()==='active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-zinc-50 text-zinc-700 ring-zinc-200'">
                                                {{ row.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <Link :href="route('plan.meal.show', { mealPlan: row.id })"
                                                  class="text-sm font-semibold text-blue-600 hover:underline">
                                                View →
                                            </Link>
                                        </td>
                                    </tr>

                                    <tr v-if="!history.length">
                                        <td class="px-6 py-6 text-zinc-500" colspan="6">No history</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <div>
                                Updated live from your current meal plan and generated shopping list.
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Active
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-zinc-400"></span> Inactive
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
