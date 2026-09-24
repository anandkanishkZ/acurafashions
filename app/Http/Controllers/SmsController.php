<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SendSmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send_bulk_sms'])->only(['index', 'send']);
    }

    public function index()
    {
        $customerCount = User::where('user_type', 'customer')->whereNotNull('phone')->count();
        return view('backend.sms.index', compact('customerCount'));
    }

    public function send(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }

        $request->validate([
            'message' => 'required|string|max:1600',
        ]);

        $recipients = User::where('user_type', 'customer')
            ->whereNotNull('phone')
            ->pluck('phone');

        $service = new SendSmsService();
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $phone) {
            $normalized = normalize_nepal_mobile_number($phone);
            if (!is_nepal_mobile_number($normalized)) {
                $failed++;
                continue;
            }

            try {
                $service->sendSMS($normalized, env('APP_NAME'), $request->message, null);
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        flash(translate('Bulk SMS sent to') . " {$sent} " . translate('customers') . " ({$failed} " . translate('skipped or failed') . ')')->success();
        return back();
    }
}
