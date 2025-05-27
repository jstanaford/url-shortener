<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Todo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form wire:submit="save">
                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="todo_title" :value="__('Title')" />
                            <x-text-input wire:model="todo_title" id="todo_title" class="block mt-1 w-full" type="text" name="todo_title" required autofocus />
                            <x-input-error :messages="$errors->get('todo_title')" class="mt-2" />
                        </div>
                        
                        <!-- Details -->
                        <div class="mb-4">
                            <x-input-label for="details" :value="__('Details')" />
                            <textarea wire:model="details" id="details" name="details" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3"></textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-2" />
                        </div>
                        
                        <!-- Due Date -->
                        <div class="mb-4">
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input wire:model="due_date" id="due_date" class="block mt-1 w-full" type="date" name="due_date" required />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                        
                        <!-- Recurring -->
                        <div class="mb-4">
                            <div class="flex items-center">
                                <input wire:model="recurring" id="recurring" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <x-input-label for="recurring" :value="__('Recurring')" class="ml-2" />
                            </div>
                            <x-input-error :messages="$errors->get('recurring')" class="mt-2" />
                        </div>
                        
                        <!-- Recurring Schedule -->
                        <div class="mb-4" x-data="{ open: @entangle('recurring') }">
                            <div x-show="open">
                                <x-input-label for="recurring_schedule" :value="__('Recurring Schedule')" />
                                <select wire:model="recurring_schedule" id="recurring_schedule" name="recurring_schedule" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Schedule</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <x-input-error :messages="$errors->get('recurring_schedule')" class="mt-2" />
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-4">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select wire:model="category_id" id="category_id" name="category_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_title }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('todos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                                Cancel
                            </a>
                            
                            <x-primary-button type="submit">
                                {{ __('Create Todo') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
