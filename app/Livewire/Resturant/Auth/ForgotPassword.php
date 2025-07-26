<?php

namespace App\Livewire\Resturant\Auth;

use App\Models\User;
use Livewire\Component;
use App\Traits\WhatsappTrait;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Mail;

class ForgotPassword extends Component
{
    use WhatsappTrait;

    public $new_password;
    public $new_password_confirmation;
    public $identifier; // email or whatsapp number
    public $successMessage = '';
    public $otp;
    public $otpSent = false;
    public $resetUserId;

    #[Layout('components.layouts.auth.plain')]
    public function render()
    {
        return view('livewire.resturant.auth.forgot-password');
    }

    public function sendResetLink()
    {
        $this->validate([
            'identifier' => 'required',
        ]);

        $user = User::where('email', $this->identifier)
            ->orWhere('mobile', $this->identifier)
            ->first();

        if (!$user) {
            $this->addError('identifier', 'No user found with this email or WhatsApp number.');
            return;
        }

        // Always send OTP (for both email and WhatsApp)
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        $message = "Your OTP for password reset is: $otp. Do not share this code with anyone. This OTP will expire in 5 minutes.";

        // Send OTP to email
        Mail::raw($message, function ($mail) use ($user) {
            $mail->to($user->email)
                ->subject('Password Reset OTP');
        });

        // Send OTP to WhatsApp if identifier is mobile
        if ($this->identifier == $user->mobile) {
            $this->sendText($message, $user->mobile);
        }

        $this->resetUserId = $user->id;
        $this->otpSent = true;
        $this->successMessage = 'OTP sent to your email' . ($this->identifier == $user->mobile ? ' and WhatsApp number.' : '.');
    }

    public function verifyOtpAndReset()
    {
        $this->validate([
            'otp' => 'required|digits:6',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::find($this->resetUserId);

        if (!$user || !$user->otp || $user->otp_expires_at < now()) {
            $this->addError('otp', 'OTP has expired or is invalid.');
            return;
        }

        if ($user->otp != $this->otp) {
            $this->addError('otp', 'Invalid OTP.');
            return;
        }

        $user->password = bcrypt($this->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $this->successMessage = 'Your password has been reset! You can now log in.';
        return redirect()->route('login');
        $this->otpSent = false;

    }

}
