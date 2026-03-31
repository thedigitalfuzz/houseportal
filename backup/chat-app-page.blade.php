@extends('layouts.app') <!-- your main layout -->

@section('title', 'Chat App')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Chat App</h1>
        <livewire:chat-app />
    </div>
@endsection
