@extends('layouts.app') <!-- your main layout -->

@section('title', 'Player Rankings')

@section('content')
    <div class="p-6">
        <livewire:player-rankings />
    </div>
@endsection
