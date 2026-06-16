<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a personal workspace for the new user
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => $user->name . "'s Workspace",
            'description' => 'Personal workspace',
            'owner_id' => $user->id,
        ]);

        // Attach user to workspace as owner
        $workspace->users()->attach($user->id, ['role' => 'owner']);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }
}
