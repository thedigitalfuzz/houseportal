@extends('layouts.app') <!-- your main layout -->

@section('title', 'Game Points')

@section('content')
    <div class="p-6">
        <livewire:new-game-points-table />
    </div>
@endsection
