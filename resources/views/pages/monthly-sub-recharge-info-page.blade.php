@extends('layouts.app') <!-- your main layout -->

@section('title', 'Monthly Subdistributors Recharge')

@section('content')
    <div class="p-6">
        <livewire:monthly-sub-recharge-infos-table />
    </div>
@endsection
