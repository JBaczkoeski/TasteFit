<script setup>
import {Head, Link} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    mealPlans: Array
});
</script>

<template>
    <Head title="Meal Plans"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Your Meal Plans
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-8">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-800">Saved Meal Plans</h3>
                            <p class="text-gray-500 mt-1">View and manage your past and current meal plans.</p>
                        </div>
                        <Link :href="route('plan.meal.create')"
                              class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            + Create New Plan
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-700">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600 border-b">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Calories</th>
                                <th class="px-6 py-3">Duration</th>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3">Status</th>
<!--                                <th class="px-6 py-3">Actions</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="plan in mealPlans"
                                :key="plan.id"
                                class="border-b hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4 font-medium whitespace-nowrap">
                                    <Link :href="route('plan.meal.show', plan.id)" class="text-blue-600 hover:underline">
                                        {{ plan.title }}
                                    </Link>
                                </td>                                <td class="px-6 py-4 whitespace-nowrap">{{ plan.daily_calories }} kcal</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ plan.total_days }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ plan.created_at_human }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                        :class="plan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    >
                      {{ plan.status }}
                    </span>
                                </td>
<!--                                <td class="px-6 py-4 whitespace-nowrap space-x-2">-->
<!--                                                                        <Link :href="route('plans.show', plan.id)" class="text-blue-600 hover:underline font-medium">View</Link>-->
<!--                                                                        <Link :href="route('plans.edit', plan.id)" class="text-yellow-600 hover:underline font-medium">Edit</Link>-->
<!--                                </td>-->
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="mealPlans.length === 0" class="text-center text-gray-500 mt-10">
                        No meal plans yet. Start your first one now!
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
