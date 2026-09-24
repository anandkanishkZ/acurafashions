<?php

namespace App\Http\Controllers;

use Cache;
use Crypt;
use Illuminate\Http\Request;
use App\Models\Addon;
use App\Models\BusinessSetting;
use App\Models\OtpConfiguration;

class OTPController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:otp_configurations'])->only(['loginConfigure', 'configure_index', 'updateActivationSettings', 'update_credentials']);
    }

    /**
     * OTP Login Configuration page: master on/off toggle, per-flow toggles
     * (registration / login), and expiry/attempt/cooldown tuning.
     */
    public function loginConfigure()
    {
        $otpAddon = Addon::where('unique_identifier', 'otp_system')->first();

        return view('backend.otp.login_configuration', [
            'otpAddon' => $otpAddon,
            'registrationEnabled' => get_setting('otp_registration_enabled', '0') == '1',
            'loginEnabled' => get_setting('otp_login_enabled', '0') == '1',
            'expiryMinutes' => get_setting('otp_expiry_minutes', 5),
            'maxAttempts' => get_setting('otp_max_attempts', 5),
            'resendCooldown' => get_setting('otp_resend_cooldown_seconds', 60),
        ]);
    }

    public function updateActivationSettings(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }

        $request->validate([
            'otp_expiry_minutes' => 'required|integer|min:1|max:60',
            'otp_max_attempts' => 'required|integer|min:1|max:20',
            'otp_resend_cooldown_seconds' => 'required|integer|min:10|max:600',
        ]);

        $otpAddon = Addon::where('unique_identifier', 'otp_system')->first();
        if ($otpAddon !== null) {
            $otpAddon->activated = $request->has('otp_system_enabled') ? 1 : 0;
            $otpAddon->save();
            Cache::forget('addons');
        }

        $this->saveSetting('otp_registration_enabled', $request->has('otp_registration_enabled') ? '1' : '0');
        $this->saveSetting('otp_login_enabled', $request->has('otp_login_enabled') ? '1' : '0');
        $this->saveSetting('otp_expiry_minutes', $request->otp_expiry_minutes);
        $this->saveSetting('otp_max_attempts', $request->otp_max_attempts);
        $this->saveSetting('otp_resend_cooldown_seconds', $request->otp_resend_cooldown_seconds);

        flash(translate('OTP settings updated successfully'))->success();
        return back();
    }

    /**
     * OTP Configurations page: Bullet SMS credentials.
     */
    public function configure_index()
    {
        return view('backend.otp.configuration', [
            'hasBulletSmsToken' => !empty(get_setting('otp_bullet_sms_token')),
            'bulletSmsSenderId' => get_setting('otp_bullet_sms_sender_id'),
            'activeGateway' => OtpConfiguration::where('value', 1)->first(),
        ]);
    }

    public function update_credentials(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }

        $request->validate([
            'bullet_sms_token' => 'nullable|string|max:500',
            'bullet_sms_sender_id' => 'nullable|string|max:100',
        ]);

        // Only overwrite the stored token if the admin actually typed a new
        // one; the form never redisplays the decrypted value, so a blank
        // submit must not wipe out an already-configured token.
        if ($request->filled('bullet_sms_token')) {
            $this->saveSetting('otp_bullet_sms_token', Crypt::encryptString($request->bullet_sms_token));
        }
        $this->saveSetting('otp_bullet_sms_sender_id', (string) $request->bullet_sms_sender_id);

        // Bullet SMS is the only shipped gateway today; make sure it's the
        // active one so SendSmsService resolves it.
        OtpConfiguration::query()->update(['value' => 0]);
        OtpConfiguration::updateOrCreate(['type' => 'bullet_sms'], ['value' => 1]);

        flash(translate('SMS gateway credentials updated successfully'))->success();
        return back();
    }

    protected function saveSetting($type, $value)
    {
        $setting = BusinessSetting::where('type', $type)->first();
        if ($setting === null) {
            $setting = new BusinessSetting;
            $setting->type = $type;
        }
        $setting->value = $value;
        $setting->save();

        Cache::forget('business_settings');
    }
}
