@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h1 class="h3">{{ translate('Add Manual Payment Method') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('manual_payment_methods.store') }}" method="POST">
                @csrf
                @include('manual_payment_methods._form')
            </form>
        </div>
    </div>
@endsection
