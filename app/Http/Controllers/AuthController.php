<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request){
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Hash password and create user
        $data['password'] = Hash::make($data['password']);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
    public function login(Request $request){
        // Basic validation for login (do NOT enforce unique email here)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Attempt to log the user in
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid email or password'])
                ->withInput($request->except('password'));
        }

        // Allow only parent and teacher accounts via this login form
        $user = Auth::user();
        if (!$user || (!$user->isParent() && !$user->isTeacher())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Only parent and teacher accounts can log in here.'])
                ->withInput($request->except('password'));
        }

        // Regenerate session to prevent fixation and redirect to dashboard
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    
}
