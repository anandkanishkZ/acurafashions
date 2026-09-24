<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OtpCode;
use App\Models\SmsTemplate;
use App\Services\SendSmsService;

class OTPVerificationController extends Controller
{
    /**
     * How many minutes an OTP code stays valid for.
     */
    protected function expiryMinutes()
    {
        return (int) otp_setting('expiry_minutes', 5);
    }

    protected function maxAttempts()
    {
        return (int) otp_setting('max_attempts', 5);
    }

    protected function resendCooldownSeconds()
    {
        return (int) otp_setting('resend_cooldown_seconds', 60);
    }

    /**
     * Create (or reuse, if within the resend cooldown) an OTP code for a phone
     * number + purpose, and send it via the active SMS gateway using the given
     * sms_templates identifier.
     *
     * @return array{otp: OtpCode, sent: bool, cooldown_remaining: int}
     */
    protected function issueCode($phone, $purpose, $templateIdentifier, array $replacements = [])
    {
        $existing = OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($existing !== null) {
            $secondsSinceCreated = now()->diffInSeconds($existing->created_at);
            $cooldown = $this->resendCooldownSeconds();
            if ($secondsSinceCreated < $cooldown) {
                return ['otp' => $existing, 'sent' => false, 'cooldown_remaining' => $cooldown - $secondsSinceCreated];
            }
        }

        $code = (string) random_int(100000, 999999);

        $template = SmsTemplate::where('identifier', $templateIdentifier)->first();
        $body = $template !== null && $template->status == 1
            ? $template->sms_body
            : 'Your [[site_name]] verification code is [[code]]. It expires in [[expiry_minutes]] minutes.';

        $body = str_replace('[[code]]', $code, $body);
        $body = str_replace('[[expiry_minutes]]', $this->expiryMinutes(), $body);
        $body = str_replace('[[site_name]]', get_setting('website_name'), $body);
        foreach ($replacements as $search => $value) {
            $body = str_replace($search, $value, $body);
        }

        // Send first: only persist a code once we know it actually went out,
        // so a failed send never leaves a verifiable-but-undelivered code
        // sitting in the table (and never blocks a retry via the cooldown).
        (new SendSmsService())->sendSMS($phone, get_setting('website_name'), $body, $template->template_id ?? null);

        $otp = OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'purpose' => $purpose,
            'attempts' => 0,
            'max_attempts' => $this->maxAttempts(),
            'expires_at' => now()->addMinutes($this->expiryMinutes()),
        ]);

        return ['otp' => $otp, 'sent' => true, 'cooldown_remaining' => 0];
    }

    /**
     * Validate a submitted code for a phone + purpose. Returns the matching,
     * still-valid OtpCode on success, or null (with a flashed error) on
     * failure. Increments attempts on a wrong code.
     */
    protected function attemptVerify($phone, $purpose, $submittedCode)
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($otp === null) {
            flash(translate('No pending verification code found. Please request a new one.'))->error();
            return null;
        }

        if ($otp->isExpired()) {
            flash(translate('This code has expired. Please request a new one.'))->error();
            return null;
        }

        if ($otp->isLocked()) {
            flash(translate('Too many incorrect attempts. Please request a new code.'))->error();
            return null;
        }

        if (!hash_equals($otp->code, (string) $submittedCode)) {
            $otp->increment('attempts');
            flash(translate('Invalid verification code.'))->error();
            return null;
        }

        $otp->verified_at = now();
        $otp->save();

        return $otp;
    }

    // ------------------------------------------------------------------
    // Called from RegisterController::create() when a phone-only user signs
    // up directly (customer_registration_verify is off, otp_system is on).
    // ------------------------------------------------------------------
    public function send_code($user)
    {
        if (!is_nepal_mobile_number($user->phone)) {
            return;
        }

        try {
            $this->issueCode(normalize_nepal_mobile_number($user->phone), 'registration', 'phone_number_verification');
        } catch (\Throwable $e) {
            flash(translate('Could not send verification code. Please contact support.'))->error();
        }
    }

    // ------------------------------------------------------------------
    // Called from CustomerController when an admin creates a customer
    // account directly with a phone number.
    // ------------------------------------------------------------------
    public function account_opening($user, $password)
    {
        if (!is_nepal_mobile_number($user->phone)) {
            return;
        }

        try {
            $this->issueCode(normalize_nepal_mobile_number($user->phone), 'registration', 'phone_number_verification');
        } catch (\Throwable $e) {
            // Swallow: account creation already succeeded, SMS is best-effort.
        }
    }

    // ------------------------------------------------------------------
    // Called from NotificationUtility::sendOrderPlacedNotification().
    // ------------------------------------------------------------------
    public function send_order_code($order)
    {
        $phone = $order->user->phone ?? null;
        if ($phone === null || !is_nepal_mobile_number($phone)) {
            return;
        }

        $template = SmsTemplate::where('identifier', 'order_placement')->first();
        $body = $template !== null ? $template->sms_body : 'Thank you for your order at [[site_name]]. Your order code is [[order_code]].';
        $body = str_replace('[[order_code]]', $order->code, $body);
        $body = str_replace('[[site_name]]', get_setting('website_name'), $body);

        (new SendSmsService())->sendSMS(normalize_nepal_mobile_number($phone), get_setting('website_name'), $body, $template->template_id ?? null);
    }

    // ------------------------------------------------------------------
    // Post-registration "verify your phone" page (route('verification')),
    // linked from RegisterController::registered() when a phone-only user
    // has just signed up and needs to confirm their number.
    // ------------------------------------------------------------------
    public function verification()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = Auth::user();

        if ($user->phone === null) {
            return redirect()->route('home');
        }

        return view('auth.' . get_setting('authentication_layout_select') . '.otp_verification', [
            'phone' => $user->phone,
            'purpose' => 'registration',
            'action' => route('verification.submit'),
            'resendRoute' => route('verification.phone.resend'),
            'title' => translate('Verify Your Phone'),
        ]);
    }

    public function verify_phone(Request $request)
    {
        $user = Auth::user();
        if ($user === null) {
            return redirect()->route('user.login');
        }

        $otp = $this->attemptVerify(normalize_nepal_mobile_number($user->phone), 'registration', $request->code);
        if ($otp === null) {
            return back();
        }

        $user->verification_code = null;
        $user->phone_verified_at = now();
        $user->save();

        offerUserWelcomeCoupon();
        flash(translate('Phone number verified successfully.'))->success();
        return redirect()->route('home');
    }

    public function resend_verificcation_code()
    {
        $user = Auth::user();
        if ($user === null) {
            return redirect()->route('user.login');
        }

        try {
            $result = $this->issueCode(normalize_nepal_mobile_number($user->phone), 'registration', 'phone_number_verification');
            if ($result['sent']) {
                flash(translate('Verification code resent.'))->success();
            } else {
                flash(translate('Please wait before requesting another code.'))->warning();
            }
        } catch (\Throwable $e) {
            flash(translate('Could not resend verification code. Please contact support.'))->error();
        }

        return back();
    }

    // ------------------------------------------------------------------
    // Login with OTP: the login form's "Login With OTP" toggle posts phone
    // + country_code here instead of to the password login route.
    // ------------------------------------------------------------------
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $phone = normalize_nepal_mobile_number($request->phone);

        if (!is_nepal_mobile_number($phone)) {
            flash(translate('Login with OTP is only available for Nepal mobile numbers.'))->error();
            return back();
        }

        $countryCode = $request->country_code ?: '977';
        $fullPhone = '+' . $countryCode . $phone;

        $user = User::where('phone', $fullPhone)->first();
        if ($user === null) {
            flash(translate('No account found with this phone number.'))->error();
            return back();
        }

        try {
            $result = $this->issueCode($phone, 'login', 'login_otp');
            if (!$result['sent']) {
                flash(translate('Please wait before requesting another code.'))->warning();
            }
        } catch (\Throwable $e) {
            flash(translate('Could not send OTP. Please contact support.'))->error();
            return back();
        }

        return redirect()->route('otp-verification-page', ['phone' => $phone]);
    }

    public function otpVerificationPage(Request $request)
    {
        $phone = $request->query('phone');
        if ($phone === null) {
            return redirect()->route('user.login');
        }

        return view('auth.' . get_setting('authentication_layout_select') . '.otp_verification', [
            'phone' => $phone,
            'purpose' => 'login',
            'action' => route('validate-otp-code'),
            'resendRoute' => route('resend-otp', ['phone' => $phone]),
            'title' => translate('Enter Login Code'),
        ]);
    }

    public function resendOtp($phone)
    {
        try {
            $result = $this->issueCode(normalize_nepal_mobile_number($phone), 'login', 'login_otp');
            if ($result['sent']) {
                flash(translate('A new code has been sent.'))->success();
            } else {
                flash(translate('Please wait before requesting another code.'))->warning();
            }
        } catch (\Throwable $e) {
            flash(translate('Could not resend code. Please contact support.'))->error();
        }

        return redirect()->route('otp-verification-page', ['phone' => $phone]);
    }

    public function validateOtpCode(Request $request)
    {
        $phone = normalize_nepal_mobile_number($request->phone);

        $otp = $this->attemptVerify($phone, 'login', $request->code);
        if ($otp === null) {
            return redirect()->route('otp-verification-page', ['phone' => $phone]);
        }

        $fullPhone = '+977' . $phone;
        $user = User::where('phone', $fullPhone)->orWhere('phone', 'like', '%' . $phone)->first();

        if ($user === null) {
            flash(translate('No account found with this phone number.'))->error();
            return redirect()->route('user.login');
        }

        Auth::login($user, true);

        flash(translate('Logged in successfully.'))->success();
        return redirect()->route('dashboard');
    }

    // ------------------------------------------------------------------
    // Phone-based forgot password. GET /password/phone/reset?phone=... both
    // sends the reset code (first visit / expired) and renders the
    // code + new-password form.
    // ------------------------------------------------------------------
    public function show_reset_password_form(Request $request)
    {
        $phone = normalize_nepal_mobile_number($request->query('phone'));

        if (!is_nepal_mobile_number($phone)) {
            flash(translate('Please provide a valid Nepal mobile number.'))->error();
            return redirect()->route('user.login');
        }

        $fullPhone = '+977' . $phone;
        $user = User::where('phone', $fullPhone)->orWhere('phone', 'like', '%' . $phone)->first();
        if ($user === null) {
            flash(translate('No account found with this phone number.'))->error();
            return redirect()->route('user.login');
        }

        try {
            $this->issueCode($phone, 'password_reset', 'login_otp');
        } catch (\Throwable $e) {
            flash(translate('Could not send reset code. Please contact support.'))->error();
        }

        return view('auth.' . get_setting('authentication_layout_select') . '.otp_password_reset', [
            'phone' => $phone,
        ]);
    }

    public function reset_password_with_code(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $phone = normalize_nepal_mobile_number($request->phone);

        $otp = $this->attemptVerify($phone, 'password_reset', $request->code);
        if ($otp === null) {
            return back();
        }

        $fullPhone = '+977' . $phone;
        $user = User::where('phone', $fullPhone)->orWhere('phone', 'like', '%' . $phone)->first();

        if ($user === null) {
            flash(translate('No account found with this phone number.'))->error();
            return redirect()->route('user.login');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        flash(translate('Password reset successfully. Please login.'))->success();
        return redirect()->route('user.login');
    }
}
