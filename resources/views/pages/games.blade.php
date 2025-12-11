@extends('layouts.app') <!-- your main layout -->

@section('title', 'Games')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Games</h1>
        <livewire:games-table />
    </div>
@endsection
