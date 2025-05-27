<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Category;
use App\Models\TodoInstance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    /**
     * Display a listing of the todos.
     */
    public function index(Request $request): View
    {
        $query = Todo::query()->with('category')
            ->where('user_id', Auth::id()); // Filter todos by the authenticated user
        
        // Filter by category if provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by completion status if provided
        if ($request->has('completed')) {
            $query->where('complete', $request->boolean('completed'));
        }
        
        $todos = $query->latest()->paginate(15);
        $categories = Category::all();
        
        return view('todos.index', compact('todos', 'categories'));
    }

    /**
     * Show the form for creating a new todo.
     */
    public function create(): View
    {
        $categories = Category::all();
        return view('todos.create', compact('categories'));
    }

    /**
     * Store a newly created todo in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'todo_title' => 'required|string|max:255',
            'details' => 'nullable|string',
            'due_date' => 'required|date',
            'recurring' => 'sometimes|boolean',
            'recurring_schedule' => 'required_if:recurring,1|nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        // Set recurring to false if not provided
        $validated['recurring'] = $request->boolean('recurring');
        
        // Add user_id to the validated data
        $validated['user_id'] = Auth::id();
        
        // Create the todo
        $todo = Todo::create($validated);
        
        // If todo is recurring, create the first instance
        if ($todo->recurring) {
            TodoInstance::create([
                'todo_id' => $todo->id,
                'due_date' => $todo->due_date,
                'complete' => false,
            ]);
        }

        return redirect()->route('todos.index')
            ->with('success', 'Todo created successfully.');
    }

    /**
     * Display the specified todo.
     */
    public function show(Todo $todo): View
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todo->load(['category', 'instances', 'meta']);
        
        return view('todos.show', compact('todo'));
    }

    /**
     * Show the form for editing the specified todo.
     */
    public function edit(Todo $todo): View
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $categories = Category::all();
        return view('todos.edit', compact('todo', 'categories'));
    }

    /**
     * Update the specified todo in storage.
     */
    public function update(Request $request, Todo $todo): RedirectResponse
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'todo_title' => 'required|string|max:255',
            'details' => 'nullable|string',
            'due_date' => 'required|date',
            'recurring' => 'sometimes|boolean',
            'recurring_schedule' => 'required_if:recurring,1|nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        // Set recurring to false if not provided
        $validated['recurring'] = $request->boolean('recurring');
        
        // Handle changes to recurring status
        $wasRecurring = $todo->recurring;
        $nowRecurring = $validated['recurring'];
        
        // Update the todo
        $todo->update($validated);
        
        // If todo is now recurring but wasn't before, create the first instance
        if ($nowRecurring && !$wasRecurring) {
            TodoInstance::create([
                'todo_id' => $todo->id,
                'due_date' => $todo->due_date,
                'complete' => false,
            ]);
        }
        
        // If todo is no longer recurring, we could delete future instances
        // or keep them for historical purposes - business decision needed

        return redirect()->route('todos.index')
            ->with('success', 'Todo updated successfully.');
    }

    /**
     * Mark a todo as complete or incomplete.
     */
    public function toggleComplete(Todo $todo): RedirectResponse
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todo->update([
            'complete' => !$todo->complete,
        ]);
        
        return redirect()->back()
            ->with('success', $todo->complete ? 'Todo marked as complete.' : 'Todo marked as incomplete.');
    }

    /**
     * Remove the specified todo from storage.
     */
    public function destroy(Todo $todo): RedirectResponse
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // This will also delete all related instances and meta due to cascading
        $todo->delete();

        return redirect()->route('todos.index')
            ->with('success', 'Todo deleted successfully.');
    }
}
