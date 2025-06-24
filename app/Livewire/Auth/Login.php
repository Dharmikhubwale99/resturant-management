<?php

namespace App\Livewire\Auth;

use App\Rules\HasRoleRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    public $login;

    public $password;

    public $remember_me;

    #[Layout('components.layouts.auth.plain')]
    public function render()
    {
        return view('livewire.auth.login');
    }

    public function mount()
    {
       if (Auth::check()) {
            $user = Auth::user();
            if (in_array($user->role,['superadmin'])) {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user->role == 'client') {
                return redirect()->route('home');
            }
        }
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

        if (in_array($user->role, ['superadmin', 'subadmin'])) {
            return to_route('superadmin.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'client') {
            return to_route('home')->with('success', 'Login successfully.');
        }

        Auth::logout();
        throw ValidationException::withMessages([
            'login' => 'Unauthorized access.',
        ]);
    }
}
