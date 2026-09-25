@php
    $method = $manual_payment_method ?? null;
    $bankInfo = $method && $method->bank_info ? json_decode($method->bank_info, true) : [];
    if (empty($bankInfo)) {
        $bankInfo = [['bank_name' => '', 'account_name' => '', 'account_number' => '', 'routing_number' => '']];
    }
@endphp

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{ translate('Type') }} <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <select name="type" id="payment_type_select" class="form-control aiz-selectpicker" required onchange="toggleManualPaymentTypeFields()">
            <option value="bank" @if($method && $method->type == 'bank') selected @endif>{{ translate('Bank Payment') }}</option>
            <option value="check" @if($method && $method->type == 'check') selected @endif>{{ translate('Check Payment') }}</option>
            <option value="custom" @if($method && $method->type == 'custom') selected @endif>{{ translate('Custom Payment') }}</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{ translate('Heading') }} <span class="text-danger">*</span></label>
    <div class="col-md-9">
        <input type="text" class="form-control" name="heading" value="{{ $method->heading ?? old('heading') }}" placeholder="{{ translate('e.g. Bank Transfer') }}" required>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{ translate('Thumbnail') }}</label>
    <div class="col-md-9">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose image') }}</div>
            <input type="hidden" name="photo" class="selected-files" value="{{ $method->photo ?? '' }}">
        </div>
        <div class="file-preview box sm">
            @if($method && $method->photo)
                <img src="{{ uploaded_asset($method->photo) }}" class="img-fit" alt="">
            @endif
        </div>
    </div>
</div>

<div id="bank_info_fields" class="@if($method && $method->type != 'bank') d-none @endif">
    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Bank Accounts') }}</label>
        <div class="col-md-9">
            <div id="bank_rows">
                @foreach ($bankInfo as $row)
                    <div class="row gutters-5 mb-2 bank-row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="bank_name[]" value="{{ $row['bank_name'] ?? '' }}" placeholder="{{ translate('Bank Name') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="account_name[]" value="{{ $row['account_name'] ?? '' }}" placeholder="{{ translate('Account Name') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="account_number[]" value="{{ $row['account_number'] ?? '' }}" placeholder="{{ translate('Account Number') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="routing_number[]" value="{{ $row['routing_number'] ?? '' }}" placeholder="{{ translate('Routing Number') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-soft-danger btn-sm remove-bank-row">{{ translate('Remove') }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add_bank_row" class="btn btn-soft-primary btn-sm mt-2">
                <i class="las la-plus"></i> {{ translate('Add Another Bank Account') }}
            </button>
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{ translate('Instructions') }}</label>
    <div class="col-md-9">
        <textarea name="description" rows="4" class="form-control" placeholder="{{ translate('Instructions shown to the customer at checkout') }}">{{ $method->description ?? old('description') }}</textarea>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 col-form-label">{{ translate('Status') }}</label>
    <div class="col-md-9">
        <label class="aiz-switch mb-0">
            <input type="checkbox" name="status" value="1" @if(!$method || $method->status == 1) checked @endif>
            <span></span>
        </label>
    </div>
</div>

<div class="form-group mb-0 text-right">
    <button type="submit" class="btn btn-primary">{{ $method ? translate('Update') : translate('Save') }}</button>
</div>

<script type="text/javascript">
    function toggleManualPaymentTypeFields() {
        if ($('#payment_type_select').val() === 'bank') {
            $('#bank_info_fields').removeClass('d-none');
        } else {
            $('#bank_info_fields').addClass('d-none');
        }
    }

    $(document).on('click', '#add_bank_row', function () {
        var row = $('#bank_rows .bank-row:first').clone();
        row.find('input').val('');
        $('#bank_rows').append(row);
    });

    $(document).on('click', '.remove-bank-row', function () {
        if ($('#bank_rows .bank-row').length > 1) {
            $(this).closest('.bank-row').remove();
        }
    });
</script>
