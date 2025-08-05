<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EditProfile extends Component
{
    public $name, $email, $mobile;
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
        $this->email = $user->email;
        $this->mobile = $user->mobile;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
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
            'mobile' => $this->mobile,
            'password' => $hashPass
        ]);

        session()->flash('success', 'Profile updated successfully!');
    }
}
