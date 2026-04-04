@extends('layouts.app') <!-- your main layout -->

@section('title', 'Credentials For Game Backend')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Game Credits Credentials</h1>
        <livewire:game-credits-credentials-table />
    </div>
@endsection
