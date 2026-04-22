@extends('layouts.app') <!-- your main layout -->

@section('title', 'Overall Wallet Performance')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Overall Wallet Performance</h1>
        <livewire:wallet-performance-overall />
    </div>
@endsection
