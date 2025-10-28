<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $requested = (int) $request->input('perPage', 15);
        $perPage = $requested <= 0 ? 15 : min($requested, 1000);
        
        if ($request->input('perPage') === 'all') {
            $total = User::count();
            $perPage = min($total ?: 1, 5000);
        }
        
        $q = trim((string) $request->input('q', ''));
        $query = User::query();
        
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('role', 'like', "%{$q}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        return view('admin.users', compact('users'));
    }

    /**
     * Show the form for editing a user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => 'required|in:admin,editor,regular',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        
        // Only update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();
        
        return redirect()->route('admin.users.edit', $user->id)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role' => 'required|in:admin,editor,regular',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);
        
        return redirect()->route('admin.users')
            ->with('success', 'User created successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting your own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account!');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Delete multiple users
     */
    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('admin.users')
                ->with('error', 'No users selected!');
        }
        
        // Prevent deleting your own account
        if (in_array(auth()->id(), $ids)) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account!');
        }
        
        User::whereIn('id', $ids)->delete();
        
        return redirect()->route('admin.users')
            ->with('success', count($ids) . ' user(s) deleted successfully!');
    }
}
