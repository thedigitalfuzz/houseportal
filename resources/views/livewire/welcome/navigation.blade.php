<nav class="-mx-3 flex flex-1 justify-center">

    @if(auth()->check() || auth('staff')->check())
        <a
            href="{{ url('/dashboard') }}"
            class="inline-block px-8 py-4 bg-yellow-500 hover:bg-yellow-600 text-black font-bold text-lg rounded-lg shadow-lg transition duration-300"
        >
            Go to Dashboard
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="inline-block px-8 py-4 bg-yellow-500 hover:bg-yellow-600 text-black font-bold text-lg rounded-lg shadow-lg transition duration-300"
        >
            Login
        </a>
    @endif

</nav>
