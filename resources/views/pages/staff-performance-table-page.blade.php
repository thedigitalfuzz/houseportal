@extends('layouts.app') <!-- your main layout -->

@section('title', 'Staff Performance')

@section('content')
    <div class="p-6">
        <livewire:staff-performance-table />
    </div>
@endsection
