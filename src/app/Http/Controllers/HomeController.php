<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * Login as guest user.
     */
    public function loginAsGuest(): RedirectResponse
    {
        // Find the guest user
        $guestUser = User::where('email', 'guest@example.com')->first();
        
        if (!$guestUser) {
            return redirect()->route('home')
                ->with('error', 'Guest account not found. Please contact an administrator.');
        }
        
        // Login as the guest user
        Auth::login($guestUser);
        
        return redirect()->route('todos.index')
            ->with('success', 'Logged in as guest.');
    }
}
