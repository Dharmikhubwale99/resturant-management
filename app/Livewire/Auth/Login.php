<?php

namespace App\Livewire\Auth;

use App\Rules\HasRoleRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    public $mobile_number;

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
            if (in_array($user->role,['admin','subadmin'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role == 'client') {
                return redirect()->route('home');
            }
        }
    }

     public function submit()
    {
        $this->validate([
            'mobile_number' => 'required|exists:users,mobile_number',
            'password' => 'required|min:6',
            'remember_me' => 'nullable|boolean',
        ]);

        $creditionals = ['mobile_number' => $this->mobile_number,'password' => $this->password];

        if(!Auth::attempt($creditionals,$this->remember_me)){
            throw ValidationException::withMessages([
                'mobile_number' => 'These credentials do not match our records.',
            ]);
        }
        $user = Auth::user();

        $adminRole = ['admin', 'subadmin'];

        if($user->is_active != 0){
            Auth::logout();
            throw ValidationException::withMessages([
                'mobile_number' => 'Unauthorized access.',
            ]);
        }

        if (in_array($user->role, ['admin', 'subadmin'])) {
            return to_route('admin.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'client') {
            return to_route('home')->with('success', 'Login successfully.');
        }

        Auth::logout();
        throw ValidationException::withMessages([
            'mobile_number' => 'Unauthorized access.',
        ]);
    }
}
