<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HouseSupport Portal</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;800;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.08em;
        }

        /* Background video */
        .video-bg {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

        /* Dark overlay for readability */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: -1;
        }
    </style>
</head>

<body class="h-screen flex items-center justify-center text-white relative overflow-hidden">

<!-- Background Video -->
<video class="video-bg" autoplay muted loop playsinline>
    <!-- You can replace this video URL anytime -->
    <source src="{{ asset('videos/background.mp4') }}" type="video/mp4">
</video>

<!-- Overlay -->
<div class="overlay"></div>

<header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3 absolute top-0 w-full px-6">
    <!-- intentionally left unchanged -->
</header>

<main class="text-center px-4 z-10">
    <!-- Portal Title -->
    <h1 class="text-4xl sm:text-6xl md:text-7xl font-extrabold mb-6 tracking-widest">
        HouseSupport Portal
    </h1>

    <!-- Subtitle -->
    <p class="text-lg sm:text-xl md:text-2xl mb-10 text-gray-200">
        Use this to manage and organize your data
    </p>

    @if (Route::has('login'))
        <livewire:welcome.navigation />
    @endif
</main>

<!-- Footer -->
<footer class="absolute bottom-4 w-full text-center text-gray-300 text-sm z-10">
    &copy; Copyright Housesupport.us
</footer>

</body>
</html>
