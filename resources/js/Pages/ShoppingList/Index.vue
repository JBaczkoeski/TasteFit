<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// odbieramy plan z backendu
const props = defineProps({
    plan: {
        type: Object,
        default: null,
    },
});

const checkedItems = ref(new Set());

// sort dni po day_number
const sortedDays = computed(() => {
    if (!props.plan) return [];
    const days = props.plan.meal_plan_day || props.plan.mealPlanDay || [];
    return [...days].sort((a, b) => (a.day_number || 0) - (b.day_number || 0));
});

// agregacja listy zakupów dla konkretnego dnia
function perDayShopping(day) {
    const map = {};

    // backend może zwrócić shopping_list_items albo shoppingListItems
    const list = day.shopping_list_items || day.shoppingListItems || [];

    list.forEach(it => {
        const ingredientId = it.ingredient_id ?? it.ingredient?.id ?? null;
        const unit = it.unit || '';

        if (!ingredientId) {
            return;
        }

        const key = `${ingredientId}|${unit}`;

        if (!map[key]) {
            map[key] = {
                id: ingredientId,
                name: it.ingredient_name || it.name || it.ingredient?.name || '—',
                unit,
                amount: 0,
            };
        }

        // uwzględniamy zarówno total_amount, jak i amount
        const val = Number(it.total_amount ?? it.amount ?? 0);
        map[key].amount += isNaN(val) ? 0 : val;
    });

    // bezpieczny sort po nazwie
    return Object.values(map).sort((a, b) => (a.name || '').localeCompare(b.name || ''));
}

const toggleItem = (key) => {
    if (checkedItems.value.has(key)) {
        checkedItems.value.delete(key);
    } else {
        checkedItems.value.add(key);
    }
};

const fmt = (n) => {
    if (n == null) return '0';
    return Math.abs(n % 1) < 0.005 ? String(Math.round(n)) : n.toFixed(2);
};

const title = computed(() => props.plan?.title || 'Shopping List');
</script>
<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">
                        {{ props.plan?.title || 'Your Shopping List' }}
                    </h2>
                    <p v-if="props.plan" class="text-sm text-gray-500">
                        Days: {{ props.plan.total_days }} • Daily: {{ props.plan.daily_calories }} kcal • Meals/day:
                        {{ props.plan.daily_meals }}
                    </p>
                </div>
                <button
                    class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700"
                    type="button"
                >
                    <!-- na razie przycisk "Download List" jako placeholder -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M12 7v2H6V7H4v2H3a1 1 0 000 2h1v2H3a1 1 0 000 2h1v2h2v-2h6v2h2v-2h1a1 1 0 100-2h-1v-2h1a1 1 0 100-2h-1V7h-2z" />
                    </svg>
                    Download List
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-8 space-y-8">
                    <div v-if="!props.plan" class="text-gray-500 text-sm">
                        Brak aktywnego planu żywieniowego. Najpierw wygeneruj plan.
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Karty dla każdego dnia -->
                        <div
                            v-for="day in sortedDays"
                            :key="day.id"
                            class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-white to-gray-50 space-y-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-xl bg-gray-900 text-white grid place-items-center text-sm font-bold">
                                        D{{ day.day_number }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            Day {{ day.day_number }}
                                        </h3>
                                        <p class="text-xs text-gray-500">
                                            Estimated total: {{ day.total_calories || '—' }} kcal
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div
                                    v-for="row in perDayShopping(day)"
                                    :key="`${day.id}-${row.id}-${row.unit}`"
                                    class="flex items-start gap-2 text-sm text-gray-700 rounded-lg border border-gray-200 bg-white px-3 py-2"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="checkedItems.has(`${day.id}-${row.id}-${row.unit}`)"
                                        @change="() => toggleItem(`${day.id}-${row.id}-${row.unit}`)"
                                        class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    />
                                    <div>
                                        <span
                                            :class="{
                                                'line-through text-gray-400': checkedItems.has(`${day.id}-${row.id}-${row.unit}`),
                                            }"
                                        >
                                            {{ row.name }} — {{ fmt(row.amount) }}<span v-if="row.unit"> {{ row.unit }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opcjonalnie: zagregowana lista na cały plan (jeśli chcesz dodać w przyszłości) -->
                        <!--
                        <div class="pt-4 border-t border-gray-200">
                            ...
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

