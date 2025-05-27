<?php

namespace App\Livewire\Todos;

use App\Models\Todo;
use App\Models\Category;
use App\Models\TodoInstance;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    protected $layout = 'layouts.app';
    
    public $todo_title = '';
    public $details = '';
    public $due_date = '';
    public $recurring = false;
    public $recurring_schedule = '';
    public $category_id = '';
    
    protected $rules = [
        'todo_title' => 'required|string|max:255',
        'details' => 'nullable|string',
        'due_date' => 'required|date',
        'recurring' => 'boolean',
        'recurring_schedule' => 'required_if:recurring,true|nullable|string',
        'category_id' => 'nullable|exists:categories,id',
    ];
    
    public function mount()
    {
        // Set default due date to tomorrow
        $this->due_date = now()->addDay()->format('Y-m-d');
    }
    
    public function save()
    {
        $this->validate();
        
        // Create the todo
        $todo = Todo::create([
            'user_id' => Auth::id(),
            'todo_title' => $this->todo_title,
            'details' => $this->details,
            'due_date' => $this->due_date,
            'recurring' => $this->recurring,
            'recurring_schedule' => $this->recurring_schedule,
            'category_id' => $this->category_id ?: null,
            'complete' => false,
        ]);
        
        // If todo is recurring, create the first instance
        if ($todo->recurring) {
            TodoInstance::create([
                'todo_id' => $todo->id,
                'due_date' => $todo->due_date,
                'complete' => false,
            ]);
        }
        
        session()->flash('success', 'Todo created successfully.');
        
        return redirect()->route('todos.index');
    }
    
    public function render()
    {
        return view('livewire.todos.create', [
            'categories' => Category::all(),
        ]);
    }
}
