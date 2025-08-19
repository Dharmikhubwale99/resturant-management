<?php

namespace App\Livewire\Resturant\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Restaurant;

class Login extends Component
{
    public $username, $password, $remember_me;

    #[Layout('components.layouts.auth.plain')]
    public function render()
    {
        return view('livewire.resturant.auth.login');
    }

    public function mount()
    {
       if (Auth::check()) {
            $user = Auth::user();
            if (in_array($user->role,['superadmin'])) {
                return to_route('superadmin.dashboard')->with('success', 'Login successfully.');
            } elseif ($user->role == ['admin','waiter','kitchen']) {
                return to_route('restaurant.dashboard')->with('success', 'Login successfully.');
            }
        }
    }

    public function submit()
    {
        $this->username = trim($this->username);
        $this->password = trim($this->password);

        $this->validate([
            'username' => 'required|string',
            'password' => 'required|min:6',
            'remember_me' => 'nullable|boolean',
        ]);

        $credentials = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        if (!Auth::attempt($credentials, $this->remember_me)) {
            throw ValidationException::withMessages([
                'username' => 'These credentials do not match our records.',
            ]);
        }
        $user = Auth::user();

        if($user->is_active != 0){
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => 'Unauthorized access.',
            ]);
        }

        if (in_array($user->role, ['superadmin'])) {
            return to_route('superadmin.dashboard')->with('success', 'Login successfully.');
        }

        if ($user->role === 'admin') {
            $restaurant = Restaurant::where('user_id', $user->id)->first();
        }  elseif (in_array($user->role, ['waiter', 'kitchen', 'manager'])) {
            $restaurant = Restaurant::find($user->restaurant_id);
        } else {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Unauthorized access.',
            ]);
        }

        if (!$restaurant || (int) $restaurant->is_active !== 0) {
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => 'Your restaurant is inactive.',
            ]);
        } else {
            return to_route('restaurant.dashboard')->with('success', 'Login successfully.');
        }

    }
}
