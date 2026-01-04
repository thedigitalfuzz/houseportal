@extends('layouts.app') <!-- your main layout -->

@section('title', 'Daily Player Leaderboard')

@section('content')
    <div class="p-6">
        <livewire:player-leaderboard-daily-table />
    </div>
@endsection
