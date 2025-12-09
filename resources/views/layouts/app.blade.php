<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal')</title>

    <!-- Tailwind / CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
@include('components.sidebar')

<div class="flex-1 flex flex-col">
    <!-- Header -->
    @include('components.header')

    <!-- Main content -->
    <main class="p-6 flex-1">
        @yield('content')
    </main>
</div>
<!-- Livewire Scripts -->
@livewireScripts
</body>
</html>
