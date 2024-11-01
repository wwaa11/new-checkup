<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station @yield('station')</title>
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
    <div class="w-full flex flex-row p-3 shadow-blue-300 shadow">
        <div class="flex-shrink">
            <img src="{{ asset('images/Side Logo.png') }}" class="h-12" alt="logo">
        </div>
        <ul class="flex-grow flex p-3 gap-6 font-bold">
            <li class="text-blue-800 text-xl">B12 Check UP</li>
            <li class="ps-12">Menu 1</li>
            <li class="ps-12">Menu 2</li>
        </ul>
        <div class="flex-shrink p-3 font-bold text-red-600 cursor-pointer" onclick="logoutFn()">
            ({{ auth()->user()->userid }}) Logout
        </div>
    </div>
    <div class="w-full md:w-3/4 m-auto">
        @yield('body')
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
