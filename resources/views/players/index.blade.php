@extends('layouts.app') <!-- your main layout -->

@section('title', 'Players')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Players</h1>
        <livewire:players-table />
    </div>
@endsection
