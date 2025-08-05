<?php

namespace App\Livewire\Resturant\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\WhatsappTrait;
use Illuminate\Validation\Rule;

class Register extends Component
{
    use WhatsappTrait;

    public $name, $email, $mobile, $password, $confirm_password, $generatedOtp, $otpSentAt, $otp;
    public $showOtpForm = false;
    public $tempData = [];

    #[Layout('components.layouts.auth.plain')]
    public function render()
    {
        return view('livewire.resturant.auth.register');
    }

    public function register()
    {
        $this->validate([
           'mobile' => [
                'required',
                'numeric',
                Rule::unique('users', 'mobile')->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
            'email' => [
                'required',
                'email',
                'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
        ], [
            'email.regex' => 'Only .com email addresses are allowed.',
        ]);

        $this->generatedOtp = rand(100000, 999999);
        $this->otpSentAt = now();

        $this->showOtpForm = true;
        $this->tempData = [
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'password' => $this->password,
        ];

        $this->sendOTP($this->mobile, $this->generatedOtp);
        Notification::route('mail', $this->email)->notify(new SendOtpNotification($this->generatedOtp));

        session()->flash('success', 'OTP sent to your email. Please verify.');
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|digits:6',
            'email' => 'required|email',
        ]);

        if (!$this->tempData || $this->tempData['email'] !== $this->email) {
            session()->flash('error', 'Invalid session. Please restart registration.');
            return redirect()->to('resturant/register');
        }

        if (now()->diffInMinutes($this->otpSentAt) > 5) {
            session()->flash('error', 'OTP expired. Please register again.');
            return redirect()->back();
        }

        if ($this->otp != $this->generatedOtp) {
            session()->flash('error', 'Invalid OTP. Please try again.');
            return;
        }

        $user = User::create([
            'name' => $this->tempData['name'],
            'email' => $this->tempData['email'],
            'mobile' => $this->tempData['mobile'],
            'password' => Hash::make($this->tempData['password']),
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
            'is_active' => 0,
        ]);
        $user->assignRole('admin');

        session()->flash('success', 'Verification complete! You can now log in.');
        return redirect()->to('/');
    }

    public function resendOtp()
    {
        if (!$this->tempData || !$this->tempData['email']) {
            session()->flash('error', 'Session expired. Please register again.');
            return redirect()->to('restaurant/register');
        }

        $this->generatedOtp = rand(100000, 999999);
        $this->otpSentAt = now();

        $this->sendOTP($this->mobile, $this->generatedOtp);
        Notification::route('mail', $this->email)->notify(new SendOtpNotification($this->generatedOtp));

        session()->flash('success', 'New OTP sent to your email.');
    }
}
