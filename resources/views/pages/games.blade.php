@extends('layouts.app') <!-- your main layout -->

@section('title', 'Games')

@section('content')
    <div class="p-6">
        <livewire:games-table />
    </div>
@endsection
