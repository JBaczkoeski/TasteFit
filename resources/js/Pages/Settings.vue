<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = ref({
    defaultCalories: 2000,
    defaultDuration: 7,
    preferredDiets: ['Vegetarian'],
    preferredCuisines: ['Italian', 'Asian'],
    difficulty: 'Normal',
    notifications: true,
});

const cuisineOptions = ['Italian', 'Asian', 'Mediterranean', 'Mexican', 'Indian'];
const dietOptions = ['None', 'Vegetarian', 'Vegan', 'Keto', 'Gluten-Free'];
const difficultyOptions = ['Easy', 'Normal', 'Advanced'];

const saveSettings = () => {
    console.log('Saving plan preferences...', form.value);
};
</script>

<template>
    <Head title="Meal Plan Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Meal Plan Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-8 space-y-8">
                    <form @submit.prevent="saveSettings" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Calories</label>
                            <input v-model="form.defaultCalories" type="number" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Duration (days)</label>
                            <input v-model="form.defaultDuration" type="number" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Preferred Diets</label>
                            <div class="grid grid-cols-2 gap-3 mt-2">
                                <label v-for="option in dietOptions" :key="option" class="flex items-center gap-2">
                                    <input type="checkbox" :value="option" v-model="form.preferredDiets" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    {{ option }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Preferred Cuisines</label>
                            <div class="grid grid-cols-2 gap-3 mt-2">
                                <label v-for="option in cuisineOptions" :key="option" class="flex items-center gap-2">
                                    <input type="checkbox" :value="option" v-model="form.preferredCuisines" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    {{ option }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Plan Difficulty</label>
                            <select v-model="form.difficulty" class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                                <option v-for="option in difficultyOptions" :key="option" :value="option">{{ option }}</option>
                            </select>
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.notifications" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                            <label class="ml-2 block text-sm text-gray-700">Receive email notifications when new meal plans are generated</label>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-semibold hover:bg-green-700">Save Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
