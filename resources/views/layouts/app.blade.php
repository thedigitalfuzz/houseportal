<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        details[open] .wallet-chevron {
            transform: rotate(180deg);
        }
    </style>

    @livewireStyles

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Mobile Overlay -->
<div id="overlay" class="hidden fixed inset-0 bg-black/50 z-30 lg:hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar -->

    @include('components.sidebar')



<div class="flex-1 flex flex-col min-h-screen">

    <!-- Header -->

    @include('components.header')
    <main class="p-4 sm:p-6 flex-1">
        @yield('content')
    </main>
    @include('components.footer')
</div>

@livewireScripts
</body>
</html>
