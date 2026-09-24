@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Bullet SMS Configuration') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">{{ translate('API Token') }}</label>
                        <div class="col-md-8">
                            <input type="password" class="form-control" name="bullet_sms_token" value="" placeholder="{{ $hasBulletSmsToken ? translate('•••••••••••••••• (configured — leave blank to keep)') : translate('Paste your Bullet SMS API token') }}" autocomplete="new-password">
                            <small class="text-muted">
                                {{ translate('Copied once at creation time on the Bullet SMS dashboard. Stored encrypted; leave blank to keep the current token.') }}
                                @if ($hasBulletSmsToken)
                                    <br><span class="text-success">{{ translate('A token is currently configured.') }}</span>
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">{{ translate('Sender ID') }}</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="bullet_sms_sender_id" value="{{ $bulletSmsSenderId }}" placeholder="{{ translate('e.g. BulletSMS') }}">
                            <small class="text-muted">{{ translate('Must match an allowed sender ID on your Bullet SMS token.') }}</small>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save Configuration') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 h6">{{ translate('SMS Credits') }}</h5>
                <button type="button" id="check-balance-btn" class="btn btn-sm btn-outline-primary" onclick="checkOtpBalance()">
                    {{ translate('Check Balance') }}
                </button>
            </div>
            <div class="card-body">
                <div id="balance-result">
                    @if (!$hasBulletSmsToken)
                        <p class="mb-0 text-muted">{{ translate('Configure your Bullet SMS token first, then check your balance.') }}</p>
                    @else
                        <p class="mb-0 text-muted">{{ translate('Click "Check Balance" to fetch your current SMS credits from Bullet SMS.') }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Active Gateway') }}</h5>
            </div>
            <div class="card-body">
                @if ($activeGateway)
                    <p class="mb-0">{{ translate('Currently sending OTP messages via') }}: <strong>{{ ucwords(str_replace('_', ' ', $activeGateway->type)) }}</strong></p>
                @else
                    <p class="mb-0 text-danger">{{ translate('No SMS gateway is active. Save the configuration to activate Bullet SMS.') }}</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Notes') }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">{{ translate('Bullet SMS only delivers to Nepal mobile numbers. Other numbers cannot use OTP login/registration.') }}</li>
                    <li class="list-group-item">{{ translate('Enable the OTP system and choose which flows require it from OTP Login Configuration.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        function checkOtpBalance() {
            var $btn = $('#check-balance-btn');
            var $result = $('#balance-result');

            $btn.prop('disabled', true).text('{{ translate('Checking...') }}');

            $.get('{{ route('otp.check_balance') }}')
                .done(function (data) {
                    $result.html(
                        '<p class="mb-0"><span class="h3 mb-0 text-primary">' + data.balance + '</span> ' +
                        '<span class="text-muted">' + data.unit + ' {{ translate('remaining') }}</span></p>'
                    );
                })
                .fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : '{{ translate('Could not fetch balance.') }}';
                    $result.html('<p class="mb-0 text-danger">' + message + '</p>');
                })
                .always(function () {
                    $btn.prop('disabled', false).text('{{ translate('Check Balance') }}');
                });
        }
    </script>
@endsection
