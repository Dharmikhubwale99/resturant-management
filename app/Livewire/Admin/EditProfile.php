<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EditProfile extends Component
{
    public $name, $email, $mobile, $username;
    public $password, $confirm_password;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.edit-profile');
    }

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->mobile = $user->mobile;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
            'username' => 'required|string|max:255|unique:users,username,' . Auth::id(),
        ]);

        $user = Auth::user();
            if ($this->password) {
                $this->validate([
                    'password' => 'required|min:6',
                    'confirm_password' => 'required|same:password',
                ]);
                $hashPass = Hash::make($this->password);
            } else {
                $hashPass = $user->password;
            }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'password' => $hashPass
        ]);

        session()->flash('success', 'Profile updated successfully!');
        return redirect()->route('superadmin.dashboard');
    }
}
