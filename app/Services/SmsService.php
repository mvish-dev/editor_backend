<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $url = 'http://pay4sms.in/sendsms/';
    protected $token;

    public function __construct()
    {
        $this->token = env('SMS_API_TOKEN', 'c5ab429db8d4042074fc0c3cef75a07d');
    }

    public function sendMessage($number, $message)
    {
        // Example implementation based on pay4sms
        // URL Structure: http://pay4sms.in/sendsms/?token=<>&&credit=<>&&sender=<>&&message=<>&&number=<>
        
        $response = Http::get($this->url, [
            'token' => $this->token,
            'credit' => 2, // Assuming credit cost
            'sender' => 'EPRINT', // specific Sender ID
            'message' => $message,
            'number' => $number,
        ]);

        return $response->json();
    }
}
