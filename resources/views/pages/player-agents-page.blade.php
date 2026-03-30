@extends('layouts.app') <!-- your main layout -->

@section('title', 'Player Agents')

@section('content')
    <div class="p-6">
        <livewire:player-agents-table />
    </div>
@endsection
