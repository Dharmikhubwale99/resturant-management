<?php

namespace App\Traits;

use App\Models\WhatsappKey;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait WhatsappTrait {

    private $baseUrl = "https://sender.hubwale.in/api/";

    public function sendOTP($mobileNumber,$generatedOtp) {
        $instance_id = config('whatsapp.instance_id');
        $access_token = config('whatsapp.access_token');
        $message = "Your OTP is: $generatedOtp.\nPlease use this to complete your registration.\nPlease enter this code to complete your registration. If you did not request this, ignore this message.";

        $url = "{$this->baseUrl}send?number=91$mobileNumber&type=otp&message=$message&instance_id=$instance_id&access_token=$access_token";

        try {
            Http::timeout(30)->get($url);
        } catch (\Throwable $th) {
            info($th->getMessage());
        }
    }
}
