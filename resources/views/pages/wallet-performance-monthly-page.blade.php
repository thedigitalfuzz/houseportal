@extends('layouts.app') <!-- your main layout -->

@section('title', 'Monthly Wallet Performance')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Monthly Wallet Performance</h1>
        <livewire:wallet-performance-monthly />
    </div>
@endsection
