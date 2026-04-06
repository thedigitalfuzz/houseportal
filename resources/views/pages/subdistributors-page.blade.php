@extends('layouts.app') <!-- your main layout -->

@section('title', 'Subdistributors')

@section('content')
    <div class="p-6">
        <livewire:subdistributors-table />
    </div>
@endsection
