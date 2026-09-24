@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ translate('SMS Templates') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Identifier') }}</th>
                            <th>{{ translate('Message') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($smsTemplates as $key => $smsTemplate)
                            <tr>
                                <td>{{ ($key+1) + ($smsTemplates->currentPage() - 1)*$smsTemplates->perPage() }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $smsTemplate->identifier)) }}</td>
                                <td class="text-truncate" style="max-width: 420px;">{{ $smsTemplate->sms_body }}</td>
                                <td>
                                    @if ($smsTemplate->status == 1)
                                        <span class="badge badge-soft-success">{{ translate('Active') }}</span>
                                    @else
                                        <span class="badge badge-soft-danger">{{ translate('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                        href="{{ route('sms-templates.edit', $smsTemplate->id) }}"
                                        title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $smsTemplates->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
