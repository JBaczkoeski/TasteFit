# 🥗 TasteFit – Meal Planning App

TasteFit is a modern meal planning and grocery management application built with Laravel, Vue.js, and Inertia.js. It allows users to generate personalized meal plans, manage shopping lists, and track history, with preferences and diet settings.

---

## 🧩 Tech Stack

### Backend
- **Laravel 12** – robust and scalable PHP framework
- **Authentication & Sessions:**
  - **Laravel Breeze** – lightweight starter kit for auth scaffolding
  - **Laravel Sanctum** – for token-based API authentication

### Frontend
- **Vue.js 3** – reactive UI framework
- **Inertia.js** – for seamless SPA-like routing
- **Tailwind CSS** – utility-first CSS framework for UI
- **Headless UI & Custom Components** – flexible inputs, dropdowns, layouts

### Database
- **MySQL 8** – relational database for storing user data, plans, settings, etc.

### External APIs
- **Spoonacular API** – used for meal suggestions, nutritional data, and grocery items

---

## ⚙️ Features

### 🧠 Meal Plan Generation
- Create custom plans based on calories, duration, cuisine, and dietary restrictions
- Preferences managed in **Settings** view
- Difficulty level and notes supported

### 🛒 Smart Shopping List
- Grouped by food categories (e.g., Proteins, Vegetables, etc.)
- Items linked to specific meals and days
- Checkbox tracking with strike-through visual feedback

### 📅 Plan History
- Review previous meal plans
- See creation dates, calorie goals, duration, and status

### 📑 Meal Plan Settings
- Set default calories and duration
- Choose preferred cuisines and diets
- Enable/disable email notifications for new plans

### 🛡️ Auth & User System
- Login, register, and logout via **Laravel Breeze**
- Vue components handle frontend forms via `useForm`

---

## 🚀 Installation

1. Clone the repository:
```bash
git clone https://github.com/your-username/tastefit.git
cd tastefit
```

2. Install backend dependencies:
```bash
composer install
cp .env.example .env
php artisan key:generate
```

3. Install frontend dependencies:
```bash
npm install
npm run dev
```

4. Migrate database:
```bash
php artisan migrate
```

5. (Optional) Set up Spoonacular API key in `.env`:
```env
SPOONACULAR_API_KEY=your_key_here
```

6. Run server:
```bash
php artisan serve
```

---

## ✨ Credits
- Built with ❤️ by the Jakub Baczkowski
- Powered by Laravel, Vue 3, Inertia, Tailwind and Spoonacular

---

## 📄 License
This project is open-source under the [MIT license](LICENSE).
