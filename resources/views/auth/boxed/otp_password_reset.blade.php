@extends('auth.layouts.authentication')

@section('content')
    <div class="auth-page">
        <div class="auth-shell">
            <!-- Hero Panel (desktop only) -->
            <div class="auth-hero-panel">
                <div class="auth-hero-top">
                    <div class="auth-hero-brand">
                        @if(get_setting('site_icon'))
                            <span class="auth-hero-brand-mark">
                                <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}">
                            </span>
                        @endif
                        {{ get_setting('website_name') }}
                    </div>

                    <h1 class="auth-hero-heading">{{ translate('Forgot your password') }}? <u>{{ translate("No problem") }}.</u></h1>
                    <p class="auth-hero-sub">{{ translate('Confirm the code we texted you and choose a new password to get back into your account.') }}</p>
                </div>

                <div class="auth-hero-bottom">
                    <span class="auth-hero-badge"><span class="dot"></span> {{ translate('256-bit SSL Secured') }}</span>
                </div>
            </div>

            <!-- Form Panel -->
            <div class="auth-form-side">
            <div class="auth-card">
                <div class="auth-top-row">
                    <a href="{{ url()->previous() }}" class="auth-back-link">
                        <i class="las la-arrow-left"></i>
                    </a>
                    <span class="auth-step-pill">{{ translate('Reset Password') }}</span>
                </div>

                <div class="auth-logo-mark">
                    @if(get_setting('site_icon'))
                        <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    @endif
                    <span class="auth-logo-fallback" style="{{ get_setting('site_icon') ? 'display:none;' : '' }}">{{ strtoupper(substr(get_setting('website_name', 'A'), 0, 1)) }}</span>
                </div>

                <div class="auth-heading-block">
                    <h2 class="auth-title">{{ translate('Reset Your Password') }}</h2>
                    <h5 class="auth-subtitle">{{ translate('Enter the code sent to your phone and choose a new password') }}</h5>
                </div>

                <form class="form-default auth-form" role="form" action="{{ route('password.update.phone') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $phone }}">

                    <div class="form-group">
                        <label for="code" class="auth-label">{{ translate('Verification Code') }}</label>
                        <div class="auth-input-group">
                            <i class="las la-key auth-input-icon"></i>
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" placeholder="{{ translate('••••••') }}" name="code" autofocus>
                        </div>
                        @if ($errors->has('code'))
                            <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('code') }}</strong></span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="password" class="auth-label">{{ translate('New Password') }}</label>
                        <div class="auth-input-group">
                            <i class="las la-lock auth-input-icon"></i>
                            <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ translate('New Password') }}" name="password">
                            <i class="password-toggle las la-eye"></i>
                        </div>
                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert"><strong>{{ $errors->first('password') }}</strong></span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="auth-label">{{ translate('Confirm Password') }}</label>
                        <div class="auth-input-group">
                            <i class="las la-lock auth-input-icon"></i>
                            <input type="password" class="form-control" placeholder="{{ translate('Confirm Password') }}" name="password_confirmation">
                            <i class="password-toggle las la-eye"></i>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <button type="submit" class="btn btn-primary btn-block auth-submit-btn">{{ translate('Reset Password') }}</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
@endsection
