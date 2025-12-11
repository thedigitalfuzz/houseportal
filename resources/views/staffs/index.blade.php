@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Staff Management</h1>
        <livewire:staffs-table />
    </div>
@endsection
