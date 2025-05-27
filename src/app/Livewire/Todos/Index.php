<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $layout = 'layouts.app';
    
    public $categoryId = '';
    public $completed = '';
    
    protected $queryString = [
        'categoryId' => ['except' => ''],
        'completed' => ['except' => ''],
    ];
    
    public function updatingCategoryId()
    {
        $this->resetPage();
    }
    
    public function updatingCompleted()
    {
        $this->resetPage();
    }
    
    public function toggleComplete(Todo $todo)
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            return;
        }
        
        $todo->update([
            'complete' => !$todo->complete,
        ]);
        
        session()->flash('success', $todo->complete ? 'Todo marked as complete.' : 'Todo marked as incomplete.');
    }
    
    public function delete(Todo $todo)
    {
        // Check if the todo belongs to the authenticated user
        if ($todo->user_id !== Auth::id()) {
            return;
        }
        
        $todo->delete();
        
        session()->flash('success', 'Todo deleted successfully.');
    }

    public function render()
    {
        $query = Todo::query()->with('category')
            ->where('user_id', Auth::id());
        
        // Filter by category if provided
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        
        // Filter by completion status if provided
        if ($this->completed !== '') {
            $query->where('complete', $this->completed === 'true');
        }
        
        $todos = $query->latest()->paginate(10);
        $categories = Category::all();
        
        return view('livewire.todos.index', [
            'todos' => $todos,
            'categories' => $categories,
        ]);
    }
}
