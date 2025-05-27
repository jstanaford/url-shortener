<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Todo Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $todo->todo_title }}</h1>
                        <div class="flex items-center space-x-4 mb-4">
                            @if($todo->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $todo->category->category_title }}
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $todo->complete ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $todo->complete ? 'Complete' : 'Incomplete' }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Due: {{ $todo->due_date->format('M d, Y') }}
                            </span>
                        </div>
                        
                        @if($todo->details)
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">Details</h3>
                                <div class="mt-2 text-gray-600">
                                    {{ $todo->details }}
                                </div>
                            </div>
                        @endif
                        
                        @if($todo->recurring)
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">Recurring Schedule</h3>
                                <div class="mt-2 text-gray-600">
                                    {{ ucfirst($todo->recurring_schedule) }}
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex space-x-4">
                        <button wire:click="toggleComplete" class="inline-flex items-center px-4 py-2 {{ $todo->complete ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:border-gray-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                            @if($todo->complete)
                                Mark Incomplete
                            @else
                                Mark Complete
                            @endif
                        </button>
                        
                        <a href="{{ route('todos.edit', $todo) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:shadow-outline-indigo disabled:opacity-25 transition ease-in-out duration-150">
                            Edit
                        </a>
                        
                        <button wire:click="delete" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-red disabled:opacity-25 transition ease-in-out duration-150" onclick="confirm('Are you sure you want to delete this todo?') || event.stopImmediatePropagation()">
                            Delete
                        </button>
                        
                        <a href="{{ route('todos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">
                            Back to Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>