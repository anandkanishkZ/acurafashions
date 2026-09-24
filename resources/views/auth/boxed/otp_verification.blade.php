@extends('auth.layouts.authentication')

@section('content')
    <div class="auth-page">
        <div class="auth-shell">
            <div class="auth-card">
                <div class="auth-top-row">
                    <a href="{{ url()->previous() }}" class="auth-back-link">
                        <i class="las la-arrow-left"></i>
                    </a>
                    <span class="auth-step-pill">{{ translate('Verify') }}</span>
                </div>

                <div class="auth-logo-mark">
                    @if(get_setting('site_icon'))
                        <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    @endif
                    <span class="auth-logo-fallback" style="{{ get_setting('site_icon') ? 'display:none;' : '' }}">{{ strtoupper(substr(get_setting('website_name', 'A'), 0, 1)) }}</span>
                </div>

                <div class="auth-heading-block">
                    <h2 class="auth-title">{{ $title }}</h2>
                    <h5 class="auth-subtitle">{{ translate('Enter the 6-digit code we sent to') }} {{ $phone }}</h5>
                </div>

                <form class="form-default auth-form" id="otp-verify-form" role="form" action="{{ $action }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $phone }}">

                    <div class="form-group">
                        <label for="code" class="auth-label">{{ translate('Verification Code') }}</label>
                        <div class="auth-input-group">
                            <i class="las la-key auth-input-icon"></i>
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                                class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                                placeholder="{{ translate('••••••') }}" name="code" id="code" autofocus>
                        </div>
                        @if ($errors->has('code'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('code') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="mb-3 mt-4">
                        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">{{ translate('Verify') }}</button>
                    </div>
                </form>

                <p class="auth-footer-text">
                    {{ translate("Didn't receive the code?") }}
                    <a href="{{ $resendRoute }}" class="auth-link">{{ translate('Resend Code') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
