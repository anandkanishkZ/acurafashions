<?php

namespace App\Services\OTP;

use App\Contracts\SendSms;
use Illuminate\Support\Str;

class BulletSms implements SendSms
{
    /**
     * Bullet SMS tokens can be domain-restricted; a bare server-to-server
     * call with no Origin/Referer is rejected even from an allowed IP
     * ("Request domain not whitelisted for this token"). Send both headers
     * set to this site's own URL so a domain-restricted token still works.
     */
    protected function originHeaders()
    {
        $origin = rtrim(env('APP_URL', get_setting('site_domain', '')), '/');

        if (empty($origin)) {
            return [];
        }

        return [
            'Origin: ' . $origin,
            'Referer: ' . $origin . '/',
        ];
    }

    public function send($to, $from, $text, $template_id)
    {
        $token = otp_secret_setting('bullet_sms_token');
        $senderId = otp_setting('bullet_sms_sender_id');

        if (empty($token)) {
            throw new \RuntimeException('Bullet SMS token is not configured.');
        }

        $params = [
            'to' => [normalize_nepal_mobile_number($to)],
            'message' => $text,
            'senderId' => $senderId ?: $from,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.bulletsms.com/api/v1/sms/send');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Idempotency-Key: ' . (string) Str::uuid(),
        ], $this->originHeaders()));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('Bullet SMS request failed: ' . $curlError);
        }

        $decoded = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || empty($decoded['success'])) {
            $message = $decoded['message'] ?? ('HTTP ' . $httpCode);
            throw new \RuntimeException('Bullet SMS error: ' . $message);
        }

        return $response;
    }

    /**
     * GET /balance — returns the remaining SMS credits on the configured
     * Bullet SMS token. Throws on missing token / request failure so the
     * caller can show a clear error instead of a blank/zero balance.
     *
     * @return array{balance: int|float, unit: string}
     */
    public function getBalance()
    {
        $token = otp_secret_setting('bullet_sms_token');

        if (empty($token)) {
            throw new \RuntimeException('Bullet SMS token is not configured.');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.bulletsms.com/api/v1/balance');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ], $this->originHeaders()));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('Bullet SMS request failed: ' . $curlError);
        }

        $decoded = json_decode($response, true);

        // Bullet SMS wraps successful responses as {"success":true,"data":{"balance":..,"unit":".."},...},
        // matching the same envelope /sms/send uses — not a bare {"balance":..} at the top level.
        $data = $decoded['data'] ?? null;

        if ($httpCode < 200 || $httpCode >= 300 || empty($decoded['success']) || !is_array($data) || !array_key_exists('balance', $data)) {
            $message = $decoded['message'] ?? ('HTTP ' . $httpCode);
            throw new \RuntimeException('Bullet SMS error: ' . $message);
        }

        return [
            'balance' => $data['balance'],
            'unit' => $data['unit'] ?? 'SMS',
        ];
    }
}
