<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Praram9 Check Up</title>
    <link rel="shortcut icon" href="{{ asset('images/Logo.ico') }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="w-full flex flex-col p-3">
        @auth
            <div class="flex">
                <div class="flex-grow"></div>
                <div class="flex-shrink p-3 font-bold text-red-600 cursor-pointer text-end" onclick="logoutFn()">
                    {{ auth()->user()->name }} ({{ auth()->user()->userid }})
                    <div class="text-end">
                        Logout
                    </div>
                </div>
            </div>
        @endauth
        <img class="m-auto h-48 p-3" src="{{ asset('images/Vertical Logo.png') }}" alt="logo">
        <div class="m-auto w-full lg:w-3/4 p-3 flex flex-col">
            @yield('body')
        </div>
    </div>
</body>
<script>
    async function logoutFn() {
        const formData = new FormData();
        const res = await axios.post("{{ env('APP_URL') }}/unauth", formData);
        window.location = "{{ env('APP_URL') }}/auth";
    }
</script>
@yield('scripts')

</html>
