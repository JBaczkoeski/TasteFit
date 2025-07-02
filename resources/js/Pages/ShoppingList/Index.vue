<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const shoppingList = [
    {
        category: 'Vegetables',
        items: [
            { name: 'Broccoli (2 pcs)', meal: 'Dinner', day: 'Monday' },
            { name: 'Spinach (300g)', meal: 'Lunch', day: 'Tuesday' },
            { name: 'Carrots (500g)', meal: 'Dinner', day: 'Wednesday' },
        ],
    },
    {
        category: 'Proteins',
        items: [
            { name: 'Chicken breast (500g)', meal: 'Lunch', day: 'Monday' },
            { name: 'Salmon fillet (2 pcs)', meal: 'Dinner', day: 'Thursday' },
            { name: 'Tofu (250g)', meal: 'Lunch', day: 'Friday' },
        ],
    },
    {
        category: 'Grains',
        items: [
            { name: 'Rice (1kg)', meal: 'Dinner', day: 'Monday' },
            { name: 'Oatmeal (1kg)', meal: 'Breakfast', day: 'Wednesday' },
            { name: 'Whole grain bread (1 loaf)', meal: 'Breakfast', day: 'Monday' },
        ],
    },
    {
        category: 'Fruits',
        items: [
            { name: 'Bananas (6 pcs)', meal: 'Snack', day: 'Monday' },
            { name: 'Apples (4 pcs)', meal: 'Snack', day: 'Tuesday' },
            { name: 'Blueberries (150g)', meal: 'Breakfast', day: 'Thursday' },
        ],
    },
    {
        category: 'Dairy',
        items: [
            { name: 'Greek yogurt (500g)', meal: 'Snack', day: 'Wednesday' },
            { name: 'Milk (1L)', meal: 'Breakfast', day: 'Friday' },
            { name: 'Cheese (200g)', meal: 'Lunch', day: 'Saturday' },
        ],
    },
];

const checkedItems = ref(new Set());

const toggleItem = (item) => {
    if (checkedItems.value.has(item.name)) {
        checkedItems.value.delete(item.name);
    } else {
        checkedItems.value.add(item.name);
    }
};
</script>

<template>
    <Head title="Shopping List" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Your Shopping List
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-8 space-y-10">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">Meal Plan Items</h3>
                        <button class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M12 7v2H6V7H4v2H3a1 1 0 000 2h1v2H3a1 1 0 000 2h1v2h2v-2h6v2h2v-2h1a1 1 0 100-2h-1v-2h1a1 1 0 100-2h-1V7h-2z" />
                            </svg>
                            Download List
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="section in shoppingList"
                            :key="section.category"
                            class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-lg shadow-sm p-6"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-800">{{ section.category }}</h4>
                            </div>
                            <ul class="space-y-2">
                                <li
                                    v-for="item in section.items"
                                    :key="item.name"
                                    class="flex items-start gap-2 text-sm text-gray-700"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="checkedItems.has(item.name)"
                                        @change="() => toggleItem(item)"
                                        class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    />
                                    <div>
                    <span :class="{ 'line-through text-gray-400': checkedItems.has(item.name) }">
                      {{ item.name }}
                    </span>
                                        <div class="text-xs text-gray-500">
                                            {{ item.meal }} – {{ item.day }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
