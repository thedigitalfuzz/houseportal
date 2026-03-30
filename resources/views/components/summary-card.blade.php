{{-- resources/views/components/summary-card.blade.php --}}
@props([
    'title',
    'value',
    'color' => 'gray',
    'isNegative' => false,
    'showDollar' => true, // NEW: controls whether $ is displayed
])

<div class="bg-white shadow rounded p-4 flex-1">
    <div class="text-gray-500 text-sm font-bold">{{ $title }}</div>
    <div class="text-2xl font-bold {{ $isNegative ? 'text-red-600' : ($color=='green'?'text-green-600':($color=='red'?'text-red-600':'text-gray-800')) }}">
        @if($showDollar)
            ${{ $value }}
        @else
            {{ $value }}
        @endif
    </div>
</div>
