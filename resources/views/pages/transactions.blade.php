@extends('layouts.app')

@section('title', 'Transactions')

@section('content')

<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Transactions</h1>
    <livewire:transactions-table />
</div>

@endsection
