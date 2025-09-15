<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station @yield("station")</title>
    <link rel="shortcut icon" href="{{ asset("images/Logo.ico") }}">
    <script src="{{ asset("js/axios.min.js") }}"></script>
    <script src="{{ asset("js/jquery-3.7.1.slim.js") }}"></script>
    <script src="{{ asset("js/sweetalert2@11.js") }}"></script>
    @if (file_exists(public_path("build/manifest.json")) || file_exists(public_path("hot")))
        @vite(["resources/css/app.css", "resources/js/app.js"])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="fixed left-0 right-0 top-0 flex h-24 w-full flex-row bg-white p-3 shadow shadow-blue-300">
        <div class="flex-shrink cursor-pointer">
            <img class="h-12" src="{{ asset("images/Side Logo.png") }}" alt="logo">
            <span class="font-bold">B12 CHECK UP</span>
        </div>
        <ul class="flex flex-grow gap-6 p-3 font-bold">
            <li class="p-3 ps-12">
                <a href="{{ env("APP_URL") }}/station">Select Station</a>
            </li>
            <li class="p-3 ps-12">
                <a href="{{ env("APP_URL") }}/history?input=null&date=today">History</a>
            </li>
        </ul>
        <div class="flex-shrink cursor-pointer p-3 font-bold text-red-600" onclick="logoutFn()">
            {{ auth()->user()->name }} ({{ auth()->user()->userid }})
            <div class="text-end">Logout</div>
        </div>
    </div>
    <div class="h-24"></div>
    <div class="m-auto w-full md:w-11/12">
        @yield("body")
    </div>
</body>
<script>
    $(document).ready(function() {
        setTimeout(function() {
            location.reload();
        }, 1000 * 60 * 60 * 3);
    });

    async function logoutFn() {
        const formData = new FormData();
        const res = await axios.post("{{ env("APP_URL") }}/unauth", formData);
        window.location = "{{ env("APP_URL") }}/auth";
    }
</script>
@yield("scripts")

</html>
