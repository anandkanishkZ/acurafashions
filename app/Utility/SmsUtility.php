<?php

namespace App\Utility;

use App\Models\SmsTemplate;
use App\Services\SendSmsService;

class SmsUtility
{
    protected static function renderTemplate($identifier, array $replacements)
    {
        $template = SmsTemplate::where('identifier', $identifier)->first();

        if ($template === null || $template->status != 1) {
            return null;
        }

        $body = $template->sms_body;
        $replacements['[[site_name]]'] = get_setting('website_name');

        foreach ($replacements as $search => $value) {
            $body = str_replace($search, $value, $body);
        }

        return ['body' => $body, 'template_id' => $template->template_id];
    }

    public static function delivery_status_change($phone, $order)
    {
        $status = str_replace('_', ' ', $order->delivery_status);
        $rendered = self::renderTemplate('delivery_status_change', [
            '[[order_code]]' => $order->code,
            '[[status]]' => $status,
        ]);

        if ($rendered === null || $phone === null) {
            return;
        }

        (new SendSmsService())->sendSMS($phone, get_setting('website_name'), $rendered['body'], $rendered['template_id']);
    }

    public static function payment_status_change($phone, $order)
    {
        $status = str_replace('_', ' ', $order->payment_status);
        $rendered = self::renderTemplate('payment_status_change', [
            '[[order_code]]' => $order->code,
            '[[status]]' => $status,
        ]);

        if ($rendered === null || $phone === null) {
            return;
        }

        (new SendSmsService())->sendSMS($phone, get_setting('website_name'), $rendered['body'], $rendered['template_id']);
    }

    public static function assign_delivery_boy($phone, $orderCode)
    {
        $rendered = self::renderTemplate('assign_delivery_boy', [
            '[[order_code]]' => $orderCode,
        ]);

        if ($rendered === null || $phone === null) {
            return;
        }

        (new SendSmsService())->sendSMS($phone, get_setting('website_name'), $rendered['body'], $rendered['template_id']);
    }
}
