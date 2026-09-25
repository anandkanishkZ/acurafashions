<div class="modal-body p-4">
    @if ($manual_payment_methods->count() == 0)
        <p class="text-center text-muted mb-0">{{ translate('No offline payment methods are available right now.') }}</p>
    @else
        <form action="{{ route('order.re_payment') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="row gutters-10 mb-3">
                @foreach ($manual_payment_methods as $method)
                    <div class="col-md-6">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="{{ $method->heading }}" type="radio" name="payment_option"
                                onchange="toggleOfflineRepaymentInfo({{ $method->id }})"
                                @if ($loop->first) checked @endif required>
                            <span class="d-flex align-items-center justify-content-between aiz-megabox-elem rounded-0 p-3">
                                <span class="d-block fw-400 fs-14">{{ $method->heading }}</span>
                                <span class="rounded-1 h-40px w-70px overflow-hidden">
                                    <img src="{{ uploaded_asset($method->photo) }}" class="img-fit h-100">
                                </span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>

            @foreach ($manual_payment_methods as $key => $method)
                <div id="offline_repayment_info_{{ $method->id }}" class="mb-3 @if (!$loop->first) d-none @endif">
                    @php echo $method->description @endphp
                    @if ($method->bank_info != null)
                        <ul>
                            @foreach (json_decode($method->bank_info) as $info)
                                <li>{{ translate('Bank Name') }} - {{ $info->bank_name }},
                                    {{ translate('Account Name') }} - {{ $info->account_name }},
                                    {{ translate('Account Number') }} - {{ $info->account_number }},
                                    {{ translate('Routing Number') }} - {{ $info->routing_number }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <div class="form-group row">
                <div class="col-md-3">
                    <label>{{ translate('Transaction ID') }} <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="trx_id" placeholder="{{ translate('Transaction ID') }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Photo') }}</label>
                <div class="col-md-9">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose image') }}</div>
                        <input type="hidden" name="photo" class="selected-files">
                    </div>
                    <div class="file-preview box sm"></div>
                </div>
            </div>

            <div class="form-group text-right mb-0">
                <button type="button" class="btn btn-sm btn-secondary rounded-0 mr-1" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-0">{{ translate('Submit') }}</button>
            </div>
        </form>

        <script type="text/javascript">
            function toggleOfflineRepaymentInfo(id) {
                $('[id^="offline_repayment_info_"]').addClass('d-none');
                $('#offline_repayment_info_' + id).removeClass('d-none');
            }
        </script>
    @endif
</div>
