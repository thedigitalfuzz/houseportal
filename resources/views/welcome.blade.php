<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HouseSupport Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        h1 {
            font-family: 'Press Start 2P', cursive;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-purple-900 via-indigo-900 to-blue-900 h-screen flex items-center justify-center">

<div class="text-center text-white px-4">
    <!-- Portal Title -->
    <h1 class="text-4xl sm:text-6xl md:text-7xl font-bold mb-6 animate-pulse">HouseSupport Portal</h1>

    <!-- Subtitle -->
    <p class="text-lg sm:text-xl md:text-2xl mb-10">Use this to manage and organize your data</p>

    <!-- Login Button -->
    <a href="{{ url('/login') }}" class="inline-block px-8 py-4 bg-yellow-500 hover:bg-yellow-600 text-black font-bold text-lg rounded-lg shadow-lg transition duration-300">
        Login
    </a>
</div>

<!-- Footer -->
<footer class="absolute bottom-4 w-full text-center text-gray-300 text-sm">
    &copy; Copyright Housesupport.us
</footer>

</body>
</html>
