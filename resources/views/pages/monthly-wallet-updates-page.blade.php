@extends('layouts.app') <!-- your main layout -->

@section('title', 'Monthly Wallet Updates')

@section('content')
    <div class="p-6">
        <livewire:monthly-wallet-updates-table />
    </div>
@endsection
