<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoMeta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TodoMetaController extends Controller
{
    /**
     * Display a listing of the todo meta for a specific todo.
     */
    public function index(Request $request): View
    {
        $query = TodoMeta::query()
            ->with('todo')
            ->whereHas('todo', function($query) {
                $query->where('user_id', Auth::id());
            });
        
        if ($request->filled('todo_id')) {
            $todo = Todo::findOrFail($request->todo_id);
            
            // Check if the todo belongs to the authenticated user
            if ($todo->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
            
            $query->where('todo_id', $request->todo_id);
        }
        
        $metadata = $query->orderBy('meta_key')->paginate(20);
        
        return view('todo_meta.index', [
            'metadata' => $metadata,
            'todo' => $todo ?? null,
        ]);
    }

    /**
     * Show the form for creating a new meta entry.
     */
    public function create(Request $request): View
    {
        $todoId = $request->query('todo_id');
        
        // If a specific todo is requested, verify it belongs to the user
        if ($todoId) {
            $todo = Todo::findOrFail($todoId);
            if ($todo->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            $todo = null;
        }
        
        // Only show todos that belong to the authenticated user
        $todos = Todo::where('user_id', Auth::id())->get();
        
        return view('todo_meta.create', compact('todos', 'todo'));
    }

    /**
     * Store a newly created meta entry in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'todo_id' => 'required|exists:todos,id',
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);
        
        // Check if the todo belongs to the authenticated user
        $todo = Todo::findOrFail($validated['todo_id']);
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if meta entry with same key already exists for this todo
        $existingMeta = TodoMeta::where('todo_id', $validated['todo_id'])
            ->where('meta_key', $validated['meta_key'])
            ->first();
            
        if ($existingMeta) {
            // Update existing meta entry
            $existingMeta->update([
                'meta_value' => $validated['meta_value'],
            ]);
            
            $message = 'Meta entry updated successfully.';
        } else {
            // Create new meta entry
            TodoMeta::create($validated);
            
            $message = 'Meta entry created successfully.';
        }

        return redirect()->route('todo_meta.index', ['todo_id' => $validated['todo_id']])
            ->with('success', $message);
    }

    /**
     * Display the specified meta entry.
     */
    public function show(TodoMeta $todoMeta): View
    {
        // Check if the meta entry belongs to a todo owned by the authenticated user
        if ($todoMeta->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoMeta->load('todo');
        
        return view('todo_meta.show', [
            'meta' => $todoMeta,
        ]);
    }

    /**
     * Show the form for editing the specified meta entry.
     */
    public function edit(TodoMeta $todoMeta): View
    {
        // Check if the meta entry belongs to a todo owned by the authenticated user
        if ($todoMeta->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoMeta->load('todo');
        
        return view('todo_meta.edit', [
            'meta' => $todoMeta,
        ]);
    }

    /**
     * Update the specified meta entry in storage.
     */
    public function update(Request $request, TodoMeta $todoMeta): RedirectResponse
    {
        // Check if the meta entry belongs to a todo owned by the authenticated user
        if ($todoMeta->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);
        
        // If key is changing, check if it conflicts with an existing key
        if ($validated['meta_key'] !== $todoMeta->meta_key) {
            $existingMeta = TodoMeta::where('todo_id', $todoMeta->todo_id)
                ->where('meta_key', $validated['meta_key'])
                ->where('id', '!=', $todoMeta->id)
                ->exists();
                
            if ($existingMeta) {
                return redirect()->back()
                    ->withErrors(['meta_key' => 'A meta entry with this key already exists for this todo.'])
                    ->withInput();
            }
        }
        
        $todoMeta->update($validated);

        return redirect()->route('todo_meta.index', ['todo_id' => $todoMeta->todo_id])
            ->with('success', 'Meta entry updated successfully.');
    }

    /**
     * Remove the specified meta entry from storage.
     */
    public function destroy(TodoMeta $todoMeta): RedirectResponse
    {
        // Check if the meta entry belongs to a todo owned by the authenticated user
        if ($todoMeta->todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $todoId = $todoMeta->todo_id;
        $todoMeta->delete();

        return redirect()->route('todo_meta.index', ['todo_id' => $todoId])
            ->with('success', 'Meta entry deleted successfully.');
    }
}
