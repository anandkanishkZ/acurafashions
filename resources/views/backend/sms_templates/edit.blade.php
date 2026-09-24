@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ translate('Edit SMS Template') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ ucwords(str_replace('_', ' ', $smsTemplate->identifier)) }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('sms-templates.update', $smsTemplate->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>{{ translate('Message') }}</label>
                    <textarea class="form-control" name="sms_body" rows="4" maxlength="1600" required>{{ $smsTemplate->sms_body }}</textarea>
                    <small class="text-muted">{{ translate('Available placeholders') }}: <code>[[code]]</code>, <code>[[site_name]]</code>, <code>[[expiry_minutes]]</code>, <code>[[order_code]]</code>, <code>[[status]]</code></small>
                </div>

                <div class="form-group">
                    <label class="aiz-switch mb-0">
                        <input type="checkbox" name="status" value="1" @if($smsTemplate->status == 1) checked @endif>
                        <span></span>
                    </label>
                    <span class="ml-2">{{ translate('Active') }}</span>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
