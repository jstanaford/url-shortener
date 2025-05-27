<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Todo List Scheduler') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center">
        <!-- Hero Section -->
        <div class="w-full max-w-5xl px-4 sm:px-6 lg:px-8 py-12 bg-white rounded-lg shadow-xl">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                    <span class="block">Todo List Scheduler</span>
                    <span class="block text-indigo-600 mt-2">Stay Organized & Productive</span>
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-500">
                    Manage your tasks, set recurring schedules, and keep track of your productivity with our easy-to-use todo list application.
                </p>
                
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Error Message -->
                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600">
                        {{ session('error') }}
                    </div>
                @endif
                
                <!-- Authentication Actions -->
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    @auth
                        <!-- If already logged in -->
                        <a href="{{ route('todos.index') }}" class="w-full sm:w-auto px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                            Go to Dashboard
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                                Logout
                            </button>
                        </form>
                    @else
                        <!-- If not logged in -->
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                            Login
                        </a>
                        
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                            Register
                        </a>
                        
                        <a href="{{ route('login.guest') }}" class="w-full sm:w-auto px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                            Continue as Guest
                        </a>
                    @endauth
                </div>
            </div>
        </div>
        
        <!-- Features Section -->
        <div class="w-full max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="h-12 w-12 bg-indigo-100 rounded-md flex items-center justify-center text-indigo-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Task Management</h3>
                    <p class="mt-2 text-base text-gray-500">Create, organize, and prioritize your tasks with ease. Add details, due dates, and categories.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="h-12 w-12 bg-indigo-100 rounded-md flex items-center justify-center text-indigo-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Recurring Schedules</h3>
                    <p class="mt-2 text-base text-gray-500">Set up recurring tasks on your preferred schedule. Daily, weekly, monthly, or custom patterns.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="h-12 w-12 bg-indigo-100 rounded-md flex items-center justify-center text-indigo-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Custom Categories</h3>
                    <p class="mt-2 text-base text-gray-500">Organize your tasks with custom categories. Work, personal, shopping, or create your own.</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="w-full py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-base text-gray-500">
                    &copy; {{ date('Y') }} Todo List Scheduler. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html> 