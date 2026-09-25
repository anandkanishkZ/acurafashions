<script src="{{ static_asset('assets/js/vendors.js') }}"></script>
<script>
    (function ($) {
        // USE STRICT
        "use strict";

        AIZ.data = {
            csrf: $('meta[name="csrf-token"]').attr("content"),
            appUrl: $('meta[name="app-url"]').attr("content"),
            fileBaseUrl: $('meta[name="file-base-url"]').attr("content"),
        };
        AIZ.plugins = {
            notify: function (type = "dark", message = "") {
                $.notify(
                    {
                        // options
                        message: message,
                    },
                    {
                        // settings
                        showProgressbar: true,
                        delay: 2500,
                        mouse_over: "pause",
                        placement: {
                            from: "bottom",
                            align: "left",
                        },
                        animate: {
                            enter: "animated fadeInUp",
                            exit: "animated fadeOutDown",
                        },
                        type: type,
                        template:
                            '<div data-notify="container" class="aiz-notify alert alert-{0}" role="alert">' +
                            '<button type="button" aria-hidden="true" data-notify="dismiss" class="close"><i class="las la-times"></i></button>' +
                            '<span data-notify="message">{2}</span>' +
                            '<div class="progress" data-notify="progressbar">' +
                            '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                            "</div>" +
                            "</div>",
                    }
                );
            }
        };

    })(jQuery);
</script>
<script>
    @foreach (session('flash_notification', collect())->toArray() as $message)
        AIZ.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
    @endforeach

    $('.password-toggle').click(function(){
        var $this = $(this);
        if ($this.siblings('input').attr('type') == 'password') {
            $this.siblings('input').attr('type', 'text');
            $this.removeClass('la-eye').addClass('la-eye-slash');
        } else {
            $this.siblings('input').attr('type', 'password');
            $this.removeClass('la-eye-slash').addClass('la-eye');
        }
    });
</script>

@if (addon_is_activated('otp_system'))
    <script type="text/javascript">
        // Country Code
        //
        // OTP delivery currently only supports Bullet SMS, which only ever
        // delivers to Nepal mobile numbers (see is_nepal_mobile_number() /
        // normalize_nepal_mobile_number() in app/Http/Helpers.php). Letting
        // the phone field accept any active country was misleading: a user
        // could pick e.g. India or the US, submit a number, and their OTP
        // would silently never arrive since Bullet SMS rejects it.
        //
        // Locked to Nepal only for now. If a multi-country SMS gateway is
        // added later, restore the commented-out onlyCountries block below
        // (driven by get_active_countries()) instead of this hardcoded list.
        var isPhoneShown = true,
            input = document.querySelector("#phone-code");

        // ---- Previous multi-country configuration (kept for later use) ----
        // var countryData = window.intlTelInputGlobals.getCountryData();
        // for (var i = 0; i < countryData.length; i++) {
        //     var country = countryData[i];
        //     if (country.iso2 == 'bd') {
        //         country.dialCode = '88';
        //     }
        // }
        // var iti = intlTelInput(input, {
        //     separateDialCode: true,
        //     utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
        //     onlyCountries: @php echo get_active_countries()->pluck('code') @endphp,
        //     customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
        //         if (selectedCountryData.iso2 == 'bd') {
        //             return "01xxxxxxxxx";
        //         }
        //         return selectedCountryPlaceholder;
        //     }
        // });
        // ---------------------------------------------------------------------

        var iti = intlTelInput(input, {
            separateDialCode: true,
            utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}?1590403638580",
            onlyCountries: ['np'],
            initialCountry: 'np',
            customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                return "98xxxxxxxx";
            }
        });

        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);

        input.addEventListener("countrychange", function(e) {
            // var currentMask = e.currentTarget.placeholder;
            var country = iti.getSelectedCountryData();
            $('input[name=country_code]').val(country.dialCode);

        });

        function toggleEmailPhone(el) {
            if (isPhoneShown) {
                $('.phone-form-group').addClass('d-none');
                $('.email-form-group').removeClass('d-none');
                $('input[name=phone]').val(null);
                isPhoneShown = false;
                $(el).html('*{{ translate('Use Phone Number Instead') }}');

                $('.toggle-login-with-otp').addClass('d-none');

            } else {
                $('.phone-form-group').removeClass('d-none');
                $('.email-form-group').addClass('d-none');
                $('input[name=email]').val(null);
                isPhoneShown = true;
                $(el).html('<i>*{{ translate('Use Email Instead') }}</i>');

                $('.toggle-login-with-otp').removeClass('d-none');
            }
            
            $('.submit-button').html('{{ translate('Login') }}');
            $('.password-login-block').removeClass('d-none');
            
            var url = '{{ route('login') }}';
            $('.loginForm').attr('action', url);
        }

        function toggleLoginPassOTP() {
            $('.password-login-block').addClass('d-none');
            $('.submit-button').html('{{ translate('Login With OTP') }}');

            var url = '{{ route('send-otp') }}';
            $('.loginForm').attr('action', url);
        }
    </script> 
@endif