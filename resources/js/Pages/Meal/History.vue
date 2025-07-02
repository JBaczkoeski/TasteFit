<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const historyItems = [
    {
        name: 'Weight Loss Plan – 1500 kcal',
        created_at: '2025-06-25',
        duration: 7,
        calories: 1500,
        status: 'Completed',
    },
    {
        name: 'Muscle Gain Plan – 2800 kcal',
        created_at: '2025-06-10',
        duration: 5,
        calories: 2800,
        status: 'Completed',
    },
    {
        name: 'Balanced Diet – 2000 kcal',
        created_at: '2025-05-15',
        duration: 10,
        calories: 2000,
        status: 'Completed',
    },
];
</script>
<template>
    <Head title="History"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Plan History
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-8">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto text-sm text-left text-gray-700">
                            <thead class="bg-gray-100 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Plan Name</th>
                                <th class="px-4 py-3">Calories/day</th>
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3">Created At</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="plan in historyItems"
                                :key="plan.name"
                                class="border-b hover:bg-gray-50 transition"
                            >
                                <td class="px-4 py-3 font-medium text-gray-800">{{ plan.name }}</td>
                                <td class="px-4 py-3">{{ plan.calories }} kcal</td>
                                <td class="px-4 py-3">{{ plan.duration }} days</td>
                                <td class="px-4 py-3">{{ plan.created_at }}</td>
                                <td class="px-4 py-3 text-green-600 font-semibold">{{ plan.status }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="historyItems.length === 0" class="text-center text-gray-500 mt-6">
                        You haven't generated any meal plans yet.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
