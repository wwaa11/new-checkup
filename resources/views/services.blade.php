<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station @yield('station')</title>
    <link rel="shortcut icon" href="{{ asset('images/Logo.ico') }}">
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.slim.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="h-24"></div>
    <div class="w-full md:w-11/12 m-auto">
        <div class="text-2xl mb-3 text-center font-bold">Service Stats : <span id="result">30</span>
        </div>
        <div class="w-ful">
            @foreach ($datas as $item)
                <div class="p-3 mb-3 border rounded shadow">
                    <div>ID : {{ $item['id'] }}</div>
                    <div>JOB : {{ $item['name'] }}</div>
                    <div>Date : {{ $item['create'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="flex gap-6 w-full">
            <button onclick="createServices()"
                class="font-bold flex-grow border rounded border-red-600 p-6 text-red-600">Run
                Create</button>
            <button onclick="clearServices()"
                class="font-bold flex-grow border rounded border-red-600 p-6 text-red-600">Run
                Clear</button>
        </div>
    </div>
</body>
<script>
    var c = setInterval(showclock, 1000)

    function showclock() {
        var seconds = $('#result').html();
        seconds--;
        $('#result').html(seconds)
        if (seconds == 0) {
            clearInterval(c);
            location.reload();
        }
    }

    async function createServices() {
        await axios.post("{{ env('APP_URL') }}/dispatchCreate").then((res) => {
            location.reload();
        })
    }

    async function clearServices() {
        await axios.post("{{ env('APP_URL') }}/dispatchClear").then((res) => {
            location.reload();
        })
    }
</script>

</html>
