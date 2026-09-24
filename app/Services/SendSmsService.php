<?php

namespace App\Services;

use App\Models\OtpConfiguration;

class SendSmsService
{
    public function sendSMS($to, $from, $text, $template_id)
    {
        $configuration = OtpConfiguration::where('value', 1)->first();

        if ($configuration === null) {
            throw new \RuntimeException('No active SMS gateway is configured.');
        }

        $otp_class = __NAMESPACE__ . '\\OTP\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $configuration->type)));

        if (class_exists($otp_class)) {
            return (new $otp_class)->send($to, $from, $text, $template_id);
        }

        throw new \RuntimeException("SMS gateway class [{$otp_class}] does not exist.");
    }
}
