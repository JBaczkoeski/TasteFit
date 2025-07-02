<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Welcome back, {{ $page.props.auth.user.name }}!
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm text-gray-500">Current Plan</p>
                        <p class="mt-2 text-2xl font-bold text-green-600">2000 kcal / day</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm text-gray-500">Days Planned</p>
                        <p class="mt-2 text-2xl font-bold">7 days</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm text-gray-500">Meals Today</p>
                        <p class="mt-2 text-2xl font-bold">5 meals</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm text-gray-500">Shopping List Items</p>
                        <p class="mt-2 text-2xl font-bold">23 items</p>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Today's Meals</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold">Breakfast</h4>
                            <p class="mt-2 text-gray-600">Oatmeal with fruits</p>
                            <p class="mt-1 text-sm text-gray-400">450 kcal</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold">Lunch</h4>
                            <p class="mt-2 text-gray-600">Grilled chicken salad</p>
                            <p class="mt-1 text-sm text-gray-400">600 kcal</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold">Dinner</h4>
                            <p class="mt-2 text-gray-600">Salmon with rice and broccoli</p>
                            <p class="mt-1 text-sm text-gray-400">700 kcal</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Current Plan Overview</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold mb-2">Cuisines Selected</h4>
                            <p>Italian, Mediterranean, Asian</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold mb-2">Diet Preferences</h4>
                            <p>Gluten-Free, High Protein</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold mb-2">Goal</h4>
                            <p>Maintain weight</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="text-lg font-semibold mb-2">Plan Duration</h4>
                            <p>7 days</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Current Shopping List</h3>
                    <ul class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <li class="rounded border p-3">✔️ Chicken breast (500g)</li>
                        <li class="rounded border p-3">✔️ Broccoli (2 pcs)</li>
                        <li class="rounded border p-3">✔️ Rice (500g)</li>
                        <li class="rounded border p-3">✔️ Oatmeal (1 kg)</li>
                        <li class="rounded border p-3">✔️ Bananas (6 pcs)</li>
                        <li class="rounded border p-3">✔️ Salmon fillet (2 pcs)</li>
                    </ul>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Plan History</h3>
                    <table class="w-full table-auto border-collapse">
                        <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">Date</th>
                            <th class="border px-4 py-2">Calories</th>
                            <th class="border px-4 py-2">Duration</th>
                            <th class="border px-4 py-2">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="border px-4 py-2">2025-06-01</td>
                            <td class="border px-4 py-2">2000 kcal</td>
                            <td class="border px-4 py-2">7 days</td>
                            <td class="border px-4 py-2">Completed</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">2025-05-20</td>
                            <td class="border px-4 py-2">1800 kcal</td>
                            <td class="border px-4 py-2">5 days</td>
                            <td class="border px-4 py-2">Completed</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">2025-05-10</td>
                            <td class="border px-4 py-2">2200 kcal</td>
                            <td class="border px-4 py-2">3 days</td>
                            <td class="border px-4 py-2">Completed</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Your Progress</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Weekly Goal Completion</p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full" style="width: 80%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">80% completed</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Shopping List Progress</p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: 65%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">15 of 23 items purchased</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Tips & Inspiration</h3>
                    <div class="space-y-4">
                        <div class="border rounded p-4 hover:bg-gray-50">
                            <h4 class="font-semibold">🥗 5 Quick High-Protein Recipes</h4>
                            <p class="text-sm text-gray-500">Check out simple meals packed with protein to fuel your goals.</p>
                        </div>
                        <div class="border rounded p-4 hover:bg-gray-50">
                            <h4 class="font-semibold">🍎 How to Build a Balanced Meal</h4>
                            <p class="text-sm text-gray-500">Learn the secrets to building a meal with perfect macros.</p>
                        </div>
                        <div class="border rounded p-4 hover:bg-gray-50">
                            <h4 class="font-semibold">🔥 New! Vegan Mediterranean Recipes</h4>
                            <p class="text-sm text-gray-500">Explore our newest meal options tailored to plant-based lovers.</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-xl font-bold mb-4">Reminders</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2">
                            <div class="size-2 mt-2 rounded-full bg-green-500"></div>
                            <span>Don't forget to check your shopping list before 5 PM.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="size-2 mt-2 rounded-full bg-blue-500"></div>
                            <span>Tomorrow's breakfast: Oatmeal with banana and peanut butter 🍌🥜.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="size-2 mt-2 rounded-full bg-yellow-500"></div>
                            <span>Plan renewal in 2 days. Update your preferences if needed.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
