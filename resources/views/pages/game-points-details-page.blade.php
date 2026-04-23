@extends('layouts.app') <!-- your main layout -->

@section('title', 'Game Points and Bonus Details')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Bonus and Used Points Details</h1>
        <livewire:points-details-table />
    </div>
@endsection
