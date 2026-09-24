@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ translate('Bulk SMS') }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Compose Message') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sms.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>{{ translate('Message') }}</label>
                            <textarea class="form-control" name="message" rows="5" maxlength="1600" required placeholder="{{ translate('Type the message to send to all customers with a Nepal mobile number') }}"></textarea>
                            <small class="text-muted">{{ translate('This message can be freely edited every time you send — nothing is hardcoded.') }}</small>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('{{ translate('Send this message to all customers now?') }}');">
                                {{ translate('Send Now') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Recipients') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ translate('Customers with a phone number on file') }}: <strong>{{ $customerCount }}</strong></p>
                    <small class="text-muted">{{ translate('Only Nepal mobile numbers can actually receive SMS via Bullet SMS; others are automatically skipped.') }}</small>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('OTP Message Templates') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ translate('OTP and order-notification SMS wording is edited separately, per event, here:') }}</p>
                    <a href="{{ route('sms-templates.index') }}" class="btn btn-outline-primary btn-block">{{ translate('SMS Templates') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
