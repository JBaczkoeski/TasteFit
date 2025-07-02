<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="TasteFit - Personalized Meal Planner" />
    <div class="bg-white text-gray-800">
        <header class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-green-400 via-green-300 to-green-500">
            <div class="flex items-center gap-4">
                <img src="/storage/logo.png" alt="TasteFit Logo" class="h-10">
            </div>
            <div class="flex gap-4">
                <Link v-if="canLogin" :href="route('login')" class="px-5 py-2 bg-white text-green-600 font-semibold rounded hover:bg-gray-100">Log in</Link>
                <Link v-if="canRegister" :href="route('register')" class="px-5 py-2 bg-white text-green-600 font-semibold rounded hover:bg-gray-100">Register</Link>
            </div>
        </header>
        <section class="text-center py-20 px-6">
            <h2 class="text-5xl font-extrabold mb-6">Plan Your Meals Effortlessly</h2>
            <p class="max-w-3xl mx-auto text-xl">TasteFit helps you create personalized meal plans based on your dietary needs, preferences, and calorie goals. Get a customized plan with an auto-generated shopping list in seconds.</p>
            <div class="mt-8">
                <Link :href="route('register')" class="px-10 py-4 bg-green-500 text-white text-lg font-bold rounded hover:bg-green-600">Get Started Free</Link>
            </div>
        </section>
        <section class="max-w-6xl mx-auto py-16 px-6 grid gap-12 md:grid-cols-2">
            <div>
                <h3 class="text-4xl font-bold mb-6">Why TasteFit?</h3>
                <ul class="space-y-5 text-lg">
                    <li>✔️ Choose from cuisines like Italian, Asian, Mediterranean</li>
                    <li>✔️ Supports diets: Keto, Vegan, Gluten-Free, Vegetarian</li>
                    <li>✔️ Set your calorie goal and number of days</li>
                    <li>✔️ Get automatic shopping lists</li>
                    <li>✔️ Swap meals easily if you don't like something</li>
                    <li>✔️ See nutritional info for every meal</li>
                    <li>✔️ Full history of your plans</li>
                </ul>
            </div>
            <img src="/storage/landingPage/Baner-1.png" alt="Meal Plan Example" class="rounded-3xl shadow-md">
        </section>
        <section class="bg-gray-50 py-16 px-6">
            <div class="max-w-6xl mx-auto grid gap-12 md:grid-cols-2">
                <img src="/storage/landingPage/Baner-2.png" alt="Calorie Selection" class="rounded shadow-md">
                <div>
                    <h3 class="text-4xl font-bold mb-6">Control Your Calories & Days</h3>
                    <p class="text-lg">Whether you need 1500 kcal for weight loss or 2800 kcal for muscle gain — TasteFit adapts the plan for you.</p>
                    <p class="mt-4 text-lg">Plan for 3, 5, 7 or even 30 days. You decide — the system handles the rest.</p>
                </div>
            </div>
        </section>
        <section class="py-16 px-6">
            <div class="max-w-6xl mx-auto grid gap-12 md:grid-cols-2">
                <div>
                    <h3 class="text-4xl font-bold mb-6">How It Works</h3>
                    <ol class="list-decimal list-inside space-y-4 text-lg">
                        <li>Set your dietary preferences and calorie goals</li>
                        <li>Choose the number of days</li>
                        <li>Get a complete meal plan with recipes</li>
                        <li>Review and swap any meals</li>
                        <li>Download the shopping list or view online</li>
                        <li>Start cooking and enjoy!</li>
                    </ol>
                </div>
                <img src="/storage/landingPage/Baner-4.png" alt="How it Works" class="rounded shadow-md">
            </div>
        </section>
        <section class="bg-green-50 py-20 px-6">
            <div class="max-w-6xl mx-auto text-center">
                <h2 class="text-4xl font-bold mb-6">Start Your TasteFit Journey Today</h2>
                <p class="max-w-3xl mx-auto mb-8 text-lg">Join thousands of users creating healthy, easy, and delicious meal plans. No stress. No guesswork. Just good food tailored for you.</p>
                <Link :href="route('register')" class="px-10 py-4 bg-green-500 text-white text-lg font-bold rounded hover:bg-green-600">Create Your Plan</Link>
            </div>
        </section>
        <footer class="py-6 text-center text-sm text-gray-500">
            TasteFit © 2025
        </footer>
    </div>
</template>
