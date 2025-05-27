<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Livewire\Home;
use App\Livewire\Todos\Index as TodosIndex;
use App\Livewire\Todos\Create as TodosCreate;
use App\Livewire\Todos\Show as TodosShow;
use App\Livewire\Todos\Edit as TodosEdit;
use App\Livewire\TodoInstances\Index as TodoInstancesIndex;
use App\Livewire\TodoInstances\Create as TodoInstancesCreate;
use App\Livewire\TodoInstances\Show as TodoInstancesShow;
use App\Livewire\TodoInstances\Edit as TodoInstancesEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Show as CategoriesShow;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\UserMeta\Index as UserMetaIndex;
use App\Livewire\UserMeta\Create as UserMetaCreate;
use App\Livewire\UserMeta\Show as UserMetaShow;
use App\Livewire\UserMeta\Edit as UserMetaEdit;
use Illuminate\Support\Facades\Route;

/*
use App\Livewire\Home;
use App\Livewire\Todos\Index as TodosIndex;
use App\Livewire\Todos\Create as TodosCreate;
use App\Livewire\Todos\Show as TodosShow;
use App\Livewire\Todos\Edit as TodosEdit;
use App\Livewire\TodoInstances\Index as TodoInstancesIndex;
use App\Livewire\TodoInstances\Create as TodoInstancesCreate;
use App\Livewire\TodoInstances\Show as TodoInstancesShow;
use App\Livewire\TodoInstances\Edit as TodoInstancesEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Show as CategoriesShow;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\UserMeta\Index as UserMetaIndex;
use App\Livewire\UserMeta\Create as UserMetaCreate;
use App\Livewire\UserMeta\Show as UserMetaShow;
use App\Livewire\UserMeta\Edit as UserMetaEdit;
*/

// Home page
Route::view('/', 'welcome')->name('home');
Route::get('/login-as-guest', [HomeController::class, 'loginAsGuest'])->name('login.guest');

Route::middleware(['auth', 'verified'])->group(function () {
    // Todo routes
    Route::get('/todos', TodosIndex::class)->name('todos.index');
    Route::get('/todos/create', TodosCreate::class)->name('todos.create');
    Route::get('/todos/{todo}', TodosShow::class)->name('todos.show');
    Route::get('/todos/{todo}/edit', TodosEdit::class)->name('todos.edit');
    
    // Todo instances routes
    Route::get('/todo-instances', TodoInstancesIndex::class)->name('todo-instances.index');
    Route::get('/todo-instances/create', TodoInstancesCreate::class)->name('todo-instances.create');
    Route::get('/todo-instances/{todoInstance}', TodoInstancesShow::class)->name('todo-instances.show');
    Route::get('/todo-instances/{todoInstance}/edit', TodoInstancesEdit::class)->name('todo-instances.edit');
    
    // Category routes
    Route::get('/categories', CategoriesIndex::class)->name('categories.index');
    Route::get('/categories/create', CategoriesCreate::class)->name('categories.create');
    Route::get('/categories/{category}', CategoriesShow::class)->name('categories.show');
    Route::get('/categories/{category}/edit', CategoriesEdit::class)->name('categories.edit');
    
    // User meta routes
    Route::get('/user-meta', UserMetaIndex::class)->name('user-meta.index');
    Route::get('/user-meta/create', UserMetaCreate::class)->name('user-meta.create');
    Route::get('/user-meta/{userMeta}', UserMetaShow::class)->name('user-meta.show');
    Route::get('/user-meta/{userMeta}/edit', UserMetaEdit::class)->name('user-meta.edit');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Include the authentication routes from auth.php
require __DIR__.'/auth.php';
