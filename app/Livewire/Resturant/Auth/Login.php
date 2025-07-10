<?php

namespace App\Livewire\Resturant\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public $login, $password, $remember_me;

    #[Layout('components.layouts.auth.plain')]
    public function render()
    {
        return view('livewire.resturant.auth.login');
    }

    public function submit()
    {
        $this->validate([
            'login' => 'required|string',
            'password' => 'required|min:6',
            'remember_me' => 'nullable|boolean',
        ]);

        $fieldType = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

         $credentials = [
            $fieldType => $this->login,
            'password' => $this->password
        ];

        if (!Auth::attempt($credentials, $this->remember_me)) {
            throw ValidationException::withMessages([
                'login' => 'These credentials do not match our records.',
            ]);
        }
        $user = Auth::user();

        if($user->is_active != 0){
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Unauthorized access.',
            ]);
        }

        if (in_array($user->role, ['superadmin'])) {
            return to_route('superadmin.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'admin') {
            return to_route('restaurant.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'waiter') {
            return to_route('waiter.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'kitchen') {
            return to_route('kitchen.dashboard')->with('success', 'Login successfully.');
        }

        Auth::logout();
        throw ValidationException::withMessages([
            'login' => 'Unauthorized access.',
        ]);
    }
}
