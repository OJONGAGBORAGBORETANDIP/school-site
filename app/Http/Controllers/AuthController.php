<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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

        // Regenerate session to prevent fixation and redirect to dashboard
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
