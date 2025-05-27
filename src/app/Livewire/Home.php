<?php

namespace App\Livewire;

use Livewire\Component;

class Home extends Component
{
    // We'll set this to null since we're going to handle layout differently
    protected $layout = null;
    
    public function render()
    {
        // Just return the view - we'll make it a standalone page
        return view('livewire.home');
    }
}
