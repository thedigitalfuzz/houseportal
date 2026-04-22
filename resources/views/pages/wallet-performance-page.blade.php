@extends('layouts.app') <!-- your main layout -->

@section('title', 'Wallet Performance')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Daily Wallet Performance</h1>
        <livewire:wallet-performance />
    </div>
@endsection
