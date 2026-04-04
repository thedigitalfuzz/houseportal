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
            const overlay = document.getElementById('sidebarOverlay');
            const content = document.getElementById('mainContent');
            const btn = document.getElementById('sidebarToggleBtn');

            // MOBILE
            if (window.innerWidth < 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                return;
            }

            // DESKTOP COLLAPSE
            sidebar.classList.toggle('collapsed');

            content.classList.toggle('lg:ml-64');
            content.classList.toggle('lg:ml-20');

            // MOVE BUTTON WITH SIDEBAR
            if (sidebar.classList.contains('collapsed')) {
                btn.classList.remove('lg:left-64');
                btn.classList.add('lg:left-20');
            } else {
                btn.classList.remove('lg:left-20');
                btn.classList.add('lg:left-64');
            }
        }

        function closeSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
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
    <main id="mainContent" class="p-4 sm:p-6 flex-1 md:mt-[72px]  transition-all duration-300 lg:ml-64">

        @yield('content')
    </main>
    @include('components.footer')
</div>

@livewireScripts
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const details = document.querySelectorAll('#sidebar details');

        details.forEach(target => {
            target.addEventListener('toggle', () => {
                if (target.open) {
                    details.forEach(other => {
                        if (other !== target) {
                            other.removeAttribute('open');
                        }
                    });
                }
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    function loadLucideIcons() {
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    document.addEventListener("DOMContentLoaded", loadLucideIcons);
    document.addEventListener("livewire:navigated", loadLucideIcons);
    document.addEventListener("livewire:updated", loadLucideIcons);

    // IMPORTANT: when details dropdown opens
    document.querySelectorAll("details").forEach((detail) => {
        detail.addEventListener("toggle", function () {
            if (this.open) {
                setTimeout(loadLucideIcons, 50);
            }
        });
    });
</script>
</body>
</html>
