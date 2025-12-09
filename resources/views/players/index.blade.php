@extends('layouts.app') <!-- your main layout -->

@section('title', 'Players')

@section('content')
    <div class="p-6">
        <livewire:players-table />
    </div>
@endsection
