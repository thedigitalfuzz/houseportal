@extends('layouts.app') <!-- your main layout -->

@section('title', 'Reports')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Reports</h1>
        <livewire:housesupport-reports />
    </div>
@endsection
