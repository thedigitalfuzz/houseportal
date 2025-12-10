@extends('layouts.app') <!-- your main layout -->

@section('title', 'Wallets')

@section('content')
    <div class="p-6">
        <livewire:wallets-table />
    </div>
@endsection
