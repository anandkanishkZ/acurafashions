@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('OTP System') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('otp_configurations.update.activation') }}" method="POST">
                    @csrf
                    <div class="form-group row align-items-center">
                        <label class="col-md-4 col-form-label">{{ translate('Enable OTP System') }}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch mb-0">
                                <input type="checkbox" name="otp_system_enabled" value="1" @if($otpAddon && $otpAddon->activated) checked @endif>
                                <span></span>
                            </label>
                            <small class="d-block text-muted mt-1">{{ translate('Master switch. When off, all OTP behaviour is disabled and login/registration work exactly as without this feature.') }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row align-items-center">
                        <label class="col-md-4 col-form-label">{{ translate('Require OTP on Registration') }}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch mb-0">
                                <input type="checkbox" name="otp_registration_enabled" value="1" @if($registrationEnabled) checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-md-4 col-form-label">{{ translate('Allow Login with OTP') }}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch mb-0">
                                <input type="checkbox" name="otp_login_enabled" value="1" @if($loginEnabled) checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">{{ translate('Code Expiry (minutes)') }}</label>
                        <div class="col-md-8">
                            <input type="number" min="1" max="60" class="form-control" name="otp_expiry_minutes" value="{{ $expiryMinutes }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">{{ translate('Max Attempts') }}</label>
                        <div class="col-md-8">
                            <input type="number" min="1" max="20" class="form-control" name="otp_max_attempts" value="{{ $maxAttempts }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">{{ translate('Resend Cooldown (seconds)') }}</label>
                        <div class="col-md-8">
                            <input type="number" min="10" max="600" class="form-control" name="otp_resend_cooldown_seconds" value="{{ $resendCooldown }}">
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Next Step') }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ translate('After enabling the OTP system, configure your SMS gateway credentials here:') }}</p>
                <a href="{{ route('otp.configconfiguration') }}" class="btn btn-outline-primary btn-block">{{ translate('OTP Configurations') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
