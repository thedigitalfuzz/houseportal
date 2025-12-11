@extends('layouts.app') <!-- your main layout -->

@section('title', 'Wallets')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Wallet Details</h1>
        <livewire:wallets-table />
    </div>
@endsection
