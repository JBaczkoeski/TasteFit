<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import {ref} from "vue";

const generatedPlan = ref(null);
const form = useForm({
    name: '',
    calories: '',
    duration: '',
    meals: 5,
    cuisines: [],
    diet: '',
    difficulty: 'Normal',
});
const cuisineOptions = ['Italian', 'Asian', 'Mediterranean', 'Mexican', 'Indian'];
const dietOptions = ['None', 'Vegetarian', 'Vegan', 'Keto', 'Gluten-Free'];
const difficultyOptions = ['Easy', 'Normal', 'Advanced'];

const submit = async () => {
    try {
        const response = await axios.post('/generate/meal/plans', {
            calories: parseInt(form.calories),
            diet: form.diet,
            duration: parseInt(form.duration),
            meals: parseInt(form.meals),
            difficulty: form.difficulty,
            cuisines: form.cuisines,
            name: form.name,
        });
        generatedPlan.value = response.data;
        console.log('Generated Plan:', generatedPlan.value);
        // tu możesz np. przejść do widoku podsumowania lub zapisać do bazy
    } catch (error) {
        console.error('Plan generation failed:', error);
    }
};
</script>

<template>
    <Head title="Create Meal Plan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Create New Meal Plan
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border rounded-lg p-8">
                    <form @submit.prevent="submit" class="space-y-10">
                        <div class="space-y-6">
                            <div class="border-b pb-4">
                                <h3 class="text-xl font-semibold text-gray-800">Basic Information</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                                <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Calories per day</label>
                                    <input v-model="form.calories" type="number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
                                    <input v-model="form.duration" type="number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meals per day</label>
                                <select v-model="form.meals" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option v-for="n in [1,2,3,4,5,6]" :key="n" :value="n">{{ n }} meals</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="border-b pb-4">
                                <h3 class="text-xl font-semibold text-gray-800">Preferences</h3>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Cuisines</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label v-for="cuisine in cuisineOptions" :key="cuisine" class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" v-model="form.cuisines" :value="cuisine" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                        <span>{{ cuisine }}</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Diet Type</label>
                                <select v-model="form.diet" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option v-for="option in dietOptions" :key="option" :value="option">{{ option }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Plan Difficulty</label>
                                <select v-model="form.difficulty" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option v-for="level in difficultyOptions" :key="level" :value="level">{{ level }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-md p-6 shadow-sm border">
                            <h4 class="text-lg font-semibold text-gray-700 mb-3">Plan Summary</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li><span class="font-medium text-gray-800">Name:</span> {{ form.name || '—' }}</li>
                                <li><span class="font-medium text-gray-800">Calories/day:</span> {{ form.calories || '—' }}</li>
                                <li><span class="font-medium text-gray-800">Duration:</span> {{ form.duration || '—' }} days</li>
                                <li><span class="font-medium text-gray-800">Meals/day:</span> {{ form.meals || '—' }}</li>
                                <li><span class="font-medium text-gray-800">Cuisines:</span> {{ form.cuisines.join(', ') || '—' }}</li>
                                <li><span class="font-medium text-gray-800">Diet:</span> {{ form.diet || '—' }}</li>
                                <li><span class="font-medium text-gray-800">Difficulty:</span> {{ form.difficulty || '—' }}</li>
                            </ul>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                Generate Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
