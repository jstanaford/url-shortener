<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    protected $layout = 'layouts.app';
    
    public Todo $todo;
    
    public function mount(Todo $todo)
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->todo = $todo;
    }
    
    public function toggleComplete()
    {
        $this->todo->update([
            'complete' => !$this->todo->complete,
        ]);
        
        session()->flash('success', $this->todo->complete ? 'Todo marked as complete.' : 'Todo marked as incomplete.');
    }
    
    public function delete()
    {
        $this->todo->delete();
        
        session()->flash('success', 'Todo deleted successfully.');
        
        return redirect()->route('todos.index');
    }
    
    public function render()
    {
        $this->todo->load(['category', 'instances', 'meta']);
        
        return view('livewire.todos.show');
    }
}
