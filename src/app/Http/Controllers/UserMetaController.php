<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class UserMetaController extends Controller
{
    /**
     * Display a listing of the user's meta entries.
     */
    public function index(Request $request): View
    {
        // Determine which user's meta to display
        // Default to the authenticated user, but allow admins to view others
        $userId = $request->filled('user_id') 
            ? $request->user_id
            : Auth::id();
            
        $user = User::findOrFail($userId);
        $metadata = UserMeta::where('user_id', $userId)
            ->orderBy('meta_key')
            ->paginate(20);
        
        return view('user_meta.index', compact('metadata', 'user'));
    }

    /**
     * Show the form for creating a new meta entry.
     */
    public function create(): View
    {
        // Only allow creating meta for the authenticated user
        $user = Auth::user();
        
        return view('user_meta.create', compact('user'));
    }

    /**
     * Store a newly created meta entry in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);
        
        // Set user_id to authenticated user
        $userId = Auth::id();
        
        // Check if meta entry with same key already exists for this user
        $existingMeta = UserMeta::where('user_id', $userId)
            ->where('meta_key', $validated['meta_key'])
            ->first();
            
        if ($existingMeta) {
            // Update existing meta entry
            $existingMeta->update([
                'meta_value' => $validated['meta_value'],
            ]);
            
            $message = 'User meta entry updated successfully.';
        } else {
            // Create new meta entry
            UserMeta::create([
                'user_id' => $userId,
                'meta_key' => $validated['meta_key'],
                'meta_value' => $validated['meta_value'],
            ]);
            
            $message = 'User meta entry created successfully.';
        }

        return redirect()->route('user_meta.index')
            ->with('success', $message);
    }

    /**
     * Display the specified meta entry.
     */
    public function show(UserMeta $userMeta): View
    {
        // Check if the user is authorized to view this meta entry
        if ($userMeta->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('user_meta.show', compact('userMeta'));
    }

    /**
     * Show the form for editing the specified meta entry.
     */
    public function edit(UserMeta $userMeta): View
    {
        // Check if the user is authorized to edit this meta entry
        if ($userMeta->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('user_meta.edit', compact('userMeta'));
    }

    /**
     * Update the specified meta entry in storage.
     */
    public function update(Request $request, UserMeta $userMeta): RedirectResponse
    {
        // Check if the user is authorized to update this meta entry
        if ($userMeta->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'meta_key' => 'required|string|max:255',
            'meta_value' => 'nullable|string',
        ]);
        
        // If key is changing, check if it conflicts with an existing key
        if ($validated['meta_key'] !== $userMeta->meta_key) {
            $existingMeta = UserMeta::where('user_id', $userMeta->user_id)
                ->where('meta_key', $validated['meta_key'])
                ->where('id', '!=', $userMeta->id)
                ->exists();
                
            if ($existingMeta) {
                return redirect()->back()
                    ->withErrors(['meta_key' => 'A meta entry with this key already exists for this user.'])
                    ->withInput();
            }
        }
        
        $userMeta->update($validated);

        return redirect()->route('user_meta.index')
            ->with('success', 'User meta entry updated successfully.');
    }

    /**
     * Remove the specified meta entry from storage.
     */
    public function destroy(UserMeta $userMeta): RedirectResponse
    {
        // Check if the user is authorized to delete this meta entry
        if ($userMeta->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $userMeta->delete();

        return redirect()->route('user_meta.index')
            ->with('success', 'User meta entry deleted successfully.');
    }
}
