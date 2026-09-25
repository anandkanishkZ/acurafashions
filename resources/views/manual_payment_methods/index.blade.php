@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3 d-flex align-items-center justify-content-between">
        <h1 class="h3">{{ translate('Manual Payment Methods') }}</h1>
        @can('add_manual_payment_method')
            <a href="{{ route('manual_payment_methods.create') }}" class="btn btn-primary">
                <i class="las la-plus"></i> {{ translate('Add New') }}
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Photo') }}</th>
                            <th>{{ translate('Heading') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($manual_payment_methods as $key => $method)
                            <tr>
                                <td>{{ ($key+1) + ($manual_payment_methods->currentPage() - 1)*$manual_payment_methods->perPage() }}</td>
                                <td>
                                    <img src="{{ uploaded_asset($method->photo) }}" class="img-fit size-40px" alt="{{ $method->heading }}">
                                </td>
                                <td>{{ $method->heading }}</td>
                                <td>{{ ucfirst($method->type) }}</td>
                                <td>
                                    @if ($method->status == 1)
                                        <span class="badge badge-soft-success">{{ translate('Active') }}</span>
                                    @else
                                        <span class="badge badge-soft-danger">{{ translate('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @can('edit_manual_payment_method')
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                            href="{{ route('manual_payment_methods.edit', $method->id) }}"
                                            title="{{ translate('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete_manual_payment_method')
                                        <a class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                            href="javascript:void(0)"
                                            data-href="{{ route('manual_payment_methods.destroy', $method->id) }}"
                                            title="{{ translate('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    {{ translate('No manual payment methods added yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $manual_payment_methods->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('modals.delete_modal')
@endsection
