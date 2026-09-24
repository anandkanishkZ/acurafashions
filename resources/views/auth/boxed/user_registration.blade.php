@extends('auth.layouts.authentication')

@section('content')
    <div class="auth-page">
        <div class="row no-gutters auth-shell">
            <!-- Brand / Showcase Panel -->
            <div class="col-lg-6 auth-brand-panel">
                <div class="auth-brand-glow"></div>
                <div class="auth-brand-top">
                    <div class="auth-brand-logo">
                        @if(get_setting('site_icon'))
                            <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        @endif
                        <span class="auth-logo-fallback" style="{{ get_setting('site_icon') ? 'display:none;' : '' }}">{{ strtoupper(substr(get_setting('website_name', 'A'), 0, 1)) }}</span>
                    </div>
                    <div class="auth-brand-badge"><span class="dot"></span> {{ translate('Free & Instant Access') }}</div>
                    <h1 class="auth-brand-heading">{{ translate('Join') }} <span class="accent">{{ get_setting('website_name') }}</span> {{ translate('today') }}</h1>
                    <p class="auth-brand-sub">{{ translate('Create your free account to unlock faster checkout, order tracking and exclusive member offers.') }}</p>

                    <ul class="auth-feature-list">
                        <li>
                            <span class="auth-feature-icon"><i class="las la-bolt"></i></span>
                            <span>{{ translate('One-click checkout on every future order') }}</span>
                        </li>
                        <li>
                            <span class="auth-feature-icon"><i class="las la-gift"></i></span>
                            <span>{{ translate('Exclusive deals and early access to sales') }}</span>
                        </li>
                        <li>
                            <span class="auth-feature-icon"><i class="las la-history"></i></span>
                            <span>{{ translate('Complete order history in one dashboard') }}</span>
                        </li>
                    </ul>
                </div>

                <div class="auth-brand-bottom">
                    <div class="auth-trust-row">
                        <span class="trust-badge"><i class="las la-lock"></i> {{ translate('256-bit SSL Secured') }}</span>
                        <span class="trust-badge"><i class="las la-user-check"></i> {{ translate('Trusted Platform') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Panel -->
            <div class="col-lg-6 auth-form-panel">
              <div class="auth-form-card">
                <div class="auth-form-inner">
                    <div class="auth-form-top">
                        <a href="{{ url()->previous() }}" class="auth-back-link desktop-only">
                            <i class="las la-arrow-left"></i> {{ translate('Back') }}
                        </a>
                        <div class="auth-mobile-logo">
                            @if(get_setting('site_icon'))
                                <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}" class="img-fit h-100 w-100" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            @endif
                            <span class="auth-logo-fallback" style="{{ get_setting('site_icon') ? 'display:none;' : 'display:flex;' }}">{{ strtoupper(substr(get_setting('website_name', 'A'), 0, 1)) }}</span>
                        </div>
                    </div>

                    <div class="auth-heading-block">
                        <h2 class="auth-title">{{ translate('Create an account') }}</h2>
                        <h5 class="auth-subtitle">{{ translate('Its quick and easy') }}</h5>
                    </div>

                    <form id="reg-form" class="form-default auth-form" role="form" action="{{ route('register') }}" method="POST">
                        @csrf
                        <!-- Name -->
                        <div class="form-group">
                            <label for="name" class="auth-label">{{ translate('Full Name') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-user auth-input-icon"></i>
                                <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{ translate('Full Name') }}" name="name">
                            </div>
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>

                        @if (addon_is_activated('otp_system'))
                            @if($phone)
                                {{-- Show only the phone field if $phone exists --}}
                                <div class="form-group phone-form-group mb-1">
                                    <label for="phone" class="auth-label">{{ translate('Phone') }}</label>
                                    <div class="auth-input-group">
                                        <i class="las la-phone auth-input-icon"></i>
                                        <input type="tel" id="phone-code" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                               value="{{ $phone }}" placeholder="" name="phone" autocomplete="off" readonly>
                                    </div>
                                    <input type="hidden" name="country_code" value="">
                                </div>
                            @elseif($email)
                                {{-- Show only the email field if $email exists --}}
                                <div class="form-group email-form-group mb-1">
                                    <label for="email" class="auth-label">{{ translate('Email') }}</label>
                                    <div class="auth-input-group">
                                        <i class="las la-envelope auth-input-icon"></i>
                                        <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               value="{{ $email }}" placeholder="{{ translate('Email') }}" name="email" autocomplete="off" readonly>
                                    </div>
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            @else
                                {{-- Show both fields with the toggle button if neither email nor phone is set --}}
                                <div class="form-group phone-form-group mb-1">
                                    <label for="phone" class="auth-label">{{ translate('Phone') }}</label>
                                    <div class="auth-input-group">
                                        <i class="las la-phone auth-input-icon"></i>
                                        <input type="tel" id="phone-code" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                               value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                                    </div>
                                </div>

                                <input type="hidden" id="country_code" name="country_code" value="{{ old('country_code', 'US') }}">

                                <div class="form-group email-form-group mb-1 d-none">
                                    <label for="email" class="auth-label">{{ translate('Email') }}</label>
                                    <div class="auth-input-group">
                                        <i class="las la-envelope auth-input-icon"></i>
                                        <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               value="{{ old('email') }}" placeholder="{{ translate('Email') }}" name="email" autocomplete="off">
                                    </div>
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group text-right">
                                    <button class="btn btn-link p-0 auth-otp-switch" type="button" onclick="toggleEmailPhone(this)">
                                        <i>*{{ translate('Use Email Instead') }}</i>
                                    </button>
                                </div>
                            @endif
                        @else
                            {{-- If OTP system is disabled, show only the email field --}}
                            <div class="form-group">
                                <label for="email" class="auth-label">{{ translate('Email') }}</label>
                                <div class="auth-input-group">
                                    <i class="las la-envelope auth-input-icon"></i>
                                    <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           value="{{ $email ?? old('email') }}" placeholder="{{ translate('Email') }}" name="email" {{ $email ? 'readonly' : '' }}>
                                </div>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- password -->
                        <div class="form-group">
                            <label for="password" class="auth-label">{{ translate('Password') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-lock auth-input-icon"></i>
                                <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ translate('Password') }}" name="password">
                                <i class="password-toggle las la-eye"></i>
                            </div>
                            <div class="auth-hint">{{ translate('Password must contain at least 6 digits') }}</div>
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- password Confirm -->
                        <div class="form-group">
                            <label for="password_confirmation" class="auth-label">{{ translate('Confirm Password') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-lock auth-input-icon"></i>
                                <input type="password" class="form-control" placeholder="{{ translate('Confirm Password') }}" name="password_confirmation">
                                <i class="password-toggle las la-eye"></i>
                            </div>
                        </div>

                        <!-- Recaptcha -->
                        @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1)
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="border invalid-feedback rounded p-2 mb-3 bg-danger text-white" role="alert" style="display: block;">
                                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                </span>
                            @endif
                        @endif

                        <!-- Terms and Conditions -->
                        <div class="mb-3 mt-1">
                            <label class="aiz-checkbox">
                                <input type="checkbox" name="checkbox_example_1" required>
                                <span class="fs-12 text-gray-dark">{{ translate('By signing up you agree to our ') }} <a href="{{ route('terms') }}" class="auth-link">{{ translate('terms and conditions.') }}</a></span>
                                <span class="aiz-square-check"></span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="mb-3 mt-4">
                            <button type="submit" class="btn btn-primary btn-block auth-submit-btn">{{ translate('Create Account') }}</button>
                        </div>
                    </form>

                    <!-- Social Login -->
                    @if(get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1 || get_setting('apple_login') == 1)
                        <div class="auth-divider"><span>{{ translate('Or Join With') }}</span></div>
                        <div class="auth-social-row">
                            @if (get_setting('facebook_login') == 1)
                                <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="auth-social-btn">
                                    <i class="lab la-facebook-f"></i>
                                </a>
                            @endif
                            @if(get_setting('google_login') == 1)
                                <a href="{{ route('social.login', ['provider' => 'google']) }}" class="auth-social-btn">
                                    <i class="lab la-google"></i>
                                </a>
                            @endif
                            @if (get_setting('twitter_login') == 1)
                                <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="auth-social-btn">
                                    <i class="lab la-twitter"></i>
                                </a>
                            @endif
                            @if (get_setting('apple_login') == 1)
                                <a href="{{ route('social.login', ['provider' => 'apple']) }}" class="auth-social-btn">
                                    <i class="lab la-apple"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Log In -->
                    <p class="auth-footer-text">
                        {{ translate('Already have an account?') }}
                        <a href="{{ route('user.login') }}" class="auth-link">{{ translate('Log In') }}</a>
                    </p>

                    <div class="text-center mt-3 d-lg-none">
                        <a href="{{ url()->previous() }}" class="auth-back-link">
                            <i class="las la-arrow-left"></i> {{ translate('Back to Previous Page') }}
                        </a>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>

        <script type="text/javascript">
                document.getElementById('reg-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, {action: 'register'}).then(function(token) {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', 'g-recaptcha-response');
                            input.setAttribute('value', token);
                            e.target.appendChild(input);

                            e.target.submit();
                        });
                    });
                });
        </script>
    @endif
@endsection
