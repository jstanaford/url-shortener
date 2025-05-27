<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoInstance;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TodoInstanceController extends Controller
{
    /**
     * Display a listing of the todo instances.
     */
    public function index(Request $request): View
    {
        $query = TodoInstance::query()
            ->with('todo.category')
            ->whereHas('todo', function($query) {
                $query->where('user_id', Auth::id());
            });
        
        // Filter by todo if provided
        if ($request->filled('todo_id')) {
            $todo = Todo::findOrFail($request->todo_id);
            
            // Check if the todo belongs to the authenticated user
            if ($todo->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
            
            $query->where('todo_id', $request->todo_id);
        }
        
        // Filter by completion status if provided
        if ($request->has('completed')) {
            $query->where('complete', $request->boolean('completed'));
        }
        
        // Filter by due date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }
        
        $instances = $query->orderBy('due_date')->paginate(15);
        
        return view('todo_instances.index', compact('instances'));
    }

    /**
     * Show the form for creating a new todo instance.
     */
    public function create(Request $request): View
    {
        // Only show recurring todos that belong to the authenticated user
        $todos = Todo::where('recurring', true)
            ->where('user_id', Auth::id())
            ->get();
        
        $todoId = $request->query('todo_id');
        
        // If a specific todo is requested, verify it belongs to the user
        if ($todoId) {
            $todo = Todo::findOrFail($todoId);
            if ($todo->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        return view('todo_instances.create', compact('todos', 'todoId'));
    }

    /**
     * Store a newly created todo instance in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'todo_id' => 'required|exists:todos,id',
            'due_date' => 'required|date',
            'instance_notes' => 'nullable|string',
        ]);
        
        // Check if the todo exists and belongs to the authenticated user
        $todo = Todo::findOrFail($validated['todo_id']);
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if the todo is actually recurring
        if (!$todo->recurring) {
            return redirect()->back()
                ->withErrors(['todo_id' => 'Only recurring todos can have multiple instances.'])
                ->withInput();
        }
        
        // Create the instance
        $instance = TodoInstance::create([
            'todo_id' => $validated['todo_id'],
            'due_date' => $validated['due_date'],
            'complete' => false,
            'instance_notes' => $validated['instance_notes'] ?? null,
        ]);

        return redirect()->route('todo_instances.index', ['todo_id' => $instance->todo_id])
            ->with('success', 'Todo instance created successfully.');
    }

    /**
     * Display the specified todo instance.
     */
    public function show(TodoInstance $todoInstance): View
    {
        // Check if the instance belongs to a todo owned by the authenticated user
        if ($todoInstance->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoInstance->load('todo.category');
        
        return view('todo_instances.show', [
            'instance' => $todoInstance
        ]);
    }

    /**
     * Show the form for editing the specified todo instance.
     */
    public function edit(TodoInstance $todoInstance): View
    {
        // Check if the instance belongs to a todo owned by the authenticated user
        if ($todoInstance->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoInstance->load('todo');
        $todos = Todo::where('recurring', true)
            ->where('user_id', Auth::id())
            ->get();
        
        return view('todo_instances.edit', [
            'instance' => $todoInstance,
            'todos' => $todos
        ]);
    }

    /**
     * Update the specified todo instance in storage.
     */
    public function update(Request $request, TodoInstance $todoInstance): RedirectResponse
    {
        // Check if the instance belongs to a todo owned by the authenticated user
        if ($todoInstance->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'due_date' => 'required|date',
            'instance_notes' => 'nullable|string',
        ]);
        
        $todoInstance->update($validated);

        return redirect()->route('todo_instances.index', ['todo_id' => $todoInstance->todo_id])
            ->with('success', 'Todo instance updated successfully.');
    }

    /**
     * Mark a todo instance as complete or incomplete.
     */
    public function toggleComplete(TodoInstance $todoInstance): RedirectResponse
    {
        // Check if the instance belongs to a todo owned by the authenticated user
        if ($todoInstance->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoInstance->update([
            'complete' => !$todoInstance->complete,
        ]);
        
        return redirect()->back()
            ->with('success', $todoInstance->complete ? 
                'Todo instance marked as complete.' : 
                'Todo instance marked as incomplete.'
            );
    }

    /**
     * Remove the specified todo instance from storage.
     */
    public function destroy(TodoInstance $todoInstance): RedirectResponse
    {
        // Check if the instance belongs to a todo owned by the authenticated user
        if ($todoInstance->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoId = $todoInstance->todo_id;
        $todoInstance->delete();

        return redirect()->route('todo_instances.index', ['todo_id' => $todoId])
            ->with('success', 'Todo instance deleted successfully.');
    }
}
