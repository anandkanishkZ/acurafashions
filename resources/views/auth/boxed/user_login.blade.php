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

                    <h1 class="auth-hero-heading">{{ translate('Simplify shopping with') }} <u>{{ translate('your account') }}.</u></h1>
                    <p class="auth-hero-sub">{{ translate('Sign in to track orders, manage your wishlist and enjoy a faster, personalised checkout experience.') }}</p>

                    <ul class="auth-hero-features">
                        <li>
                            <span class="auth-hero-feature-icon"><i class="las la-shipping-fast"></i></span>
                            <span>{{ translate('Real-time order tracking and delivery updates') }}</span>
                        </li>
                        <li>
                            <span class="auth-hero-feature-icon"><i class="las la-heart"></i></span>
                            <span>{{ translate('Save your favourite items to your wishlist') }}</span>
                        </li>
                        <li>
                            <span class="auth-hero-feature-icon"><i class="las la-shield-alt"></i></span>
                            <span>{{ translate('Secure, encrypted account & payment protection') }}</span>
                        </li>
                    </ul>
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
                    <span class="auth-step-pill">{{ translate('Sign In') }}</span>
                </div>

                <div class="auth-logo-mark">
                    @if(get_setting('site_icon'))
                        <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    @endif
                    <span class="auth-logo-fallback" style="{{ get_setting('site_icon') ? 'display:none;' : '' }}">{{ strtoupper(substr(get_setting('website_name', 'A'), 0, 1)) }}</span>
                </div>

                <div class="auth-heading-block">
                    <h2 class="auth-title">{{ translate('Welcome Back!') }}</h2>
                    <h5 class="auth-subtitle">{{ translate('Login to your account to continue') }}</h5>
                </div>

                <form class="form-default loginForm auth-form" id="user-login-form" role="form" action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- Email or Phone -->
                    @if (addon_is_activated('otp_system'))
                        <div class="form-group phone-form-group mb-1">
                            <label for="phone" class="auth-label">{{ translate('Phone') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-phone auth-input-icon"></i>
                                <input type="tel" id="phone-code" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="" name="phone" autocomplete="off">
                            </div>
                        </div>

                        <input type="hidden" name="country_code" value="">

                        <div class="form-group email-form-group mb-1 d-none">
                            <label for="email" class="auth-label">{{ translate('Email') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-envelope auth-input-icon"></i>
                                <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('johndoe@example.com') }}" name="email" id="email" autocomplete="off">
                            </div>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group text-right">
                            <button class="btn btn-link p-0 auth-otp-switch" type="button" onclick="toggleEmailPhone(this)"><i>*{{ translate('Use Email Instead') }}</i></button>
                        </div>
                    @else
                        <div class="form-group">
                            <label for="email" class="auth-label">{{ translate('Email') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-envelope auth-input-icon"></i>
                                <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('johndoe@example.com') }}" name="email" id="email" autocomplete="off">
                            </div>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                    @endif

                    <div class="password-login-block">
                        <!-- password -->
                        <div class="form-group">
                            <label for="password" class="auth-label">{{ translate('Password') }}</label>
                            <div class="auth-input-group">
                                <i class="las la-lock auth-input-icon"></i>
                                <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ translate('Password') }}" name="password" id="password">
                                <i class="password-toggle las la-eye"></i>
                            </div>
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <!-- Recaptcha -->
                        @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_login') == 1)
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="border invalid-feedback rounded p-2 mb-3 bg-danger text-white" role="alert" style="display: block;">
                                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                </span>
                            @endif
                        @endif

                        <div class="row mb-2 align-items-center">
                            <!-- Remember Me -->
                            <div class="col-6">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <span class="has-transition fs-12 fw-400 text-gray-dark hov-text-primary">{{ translate('Remember Me') }}</span>
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                            <!-- Forgot password -->
                            <div class="col-6 text-right">
                                @if(get_setting('login_with_otp'))
                                    <a href="javascript:void(0);" class="text-reset fs-12 fw-400 text-gray-dark hov-text-primary toggle-login-with-otp" onclick="toggleLoginPassOTP(this)">{{ translate('Login With OTP') }} / </a>
                                @endif
                                <a href="{{ route('password.request') }}" class="auth-link fs-12"><u>{{ translate('Forgot password?') }}</u></a>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-3 mt-4">
                        <button type="submit" class="btn btn-primary btn-block auth-submit-btn submit-button">{{ translate('Login') }}</button>
                    </div>
                </form>

                <!-- DEMO MODE -->
                @if (env("DEMO_MODE") == "On")
                    <div class="auth-demo-box">
                        <span>{{ translate('Customer Account') }}</span>
                        <button class="btn btn-info btn-sm" onclick="autoFillCustomer()">{{ translate('Copy credentials') }}</button>
                    </div>
                @endif

                <!-- Social Login -->
                @if(get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1 || get_setting('apple_login') == 1)
                    <div class="auth-divider"><span>{{ translate('Or Login With') }}</span></div>
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

                <!-- Register Now -->
                <p class="auth-footer-text">
                    {{ translate('Dont have an account?') }}
                    <a href="{{ route(get_setting('customer_registration_verify') === '1' ? 'registration.verification' : 'user.registration') }}" class="auth-link">{{ translate('Register Now') }}</a>
                </p>
            </div>

            <div class="auth-perks-row">
                <span class="perk"><i class="las la-shield-alt"></i> {{ translate('Secure Login') }}</span>
                <span class="perk"><i class="las la-shipping-fast"></i> {{ translate('Fast Checkout') }}</span>
                <span class="perk"><i class="las la-headset"></i> {{ translate('24/7 Support') }}</span>
            </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function autoFillCustomer(){
            $('#email').val('customer@example.com');
            $('#password').val('123456');
        }
    </script>

    @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_login') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>

        <script type="text/javascript">
                document.getElementById('user-login-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, {action: 'register'}).then(function(token) {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', 'g-recaptcha-response');
                            input.setAttribute('value', token);
                            e.target.appendChild(input);

                            var actionInput = document.createElement('input');
                            actionInput.setAttribute('type', 'hidden');
                            actionInput.setAttribute('name', 'recaptcha_action');
                            actionInput.setAttribute('value', 'recaptcha_customer_login');
                            e.target.appendChild(actionInput);

                            e.target.submit();
                        });
                    });
                });
        </script>
    @endif
@endsection
