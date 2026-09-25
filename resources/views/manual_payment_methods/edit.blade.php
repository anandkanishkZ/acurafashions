@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h1 class="h3">{{ translate('Edit Manual Payment Method') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('manual_payment_methods.update', $manual_payment_method->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('manual_payment_methods._form')
            </form>
        </div>
    </div>
@endsection
