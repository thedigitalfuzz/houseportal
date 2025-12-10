@extends('layouts.app') <!-- your main layout -->

@section('title', 'Games')

@section('content')
    <div class="p-6">
        <livewire:game-credits-table />
    </div>
@endsection
