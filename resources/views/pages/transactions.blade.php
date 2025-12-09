@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="mb-4 flex justify-between">
        <livewire:transactions-create />
        <button
            onclick="Livewire.dispatch('open-create-transaction')"
            class="px-4 py-2 bg-green-600 text-white rounded"
        >
            New Transaction
        </button>
    </div>

    <livewire:transactions-table />
@endsection
