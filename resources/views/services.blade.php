<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services</title>
    <link rel="shortcut icon" href="{{ asset('images/Logo.ico') }}">
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.slim.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="w-full md:w-11/12 m-auto p-6">
        <div class="text-2xl mb-3 text-center font-bold">Service Stats : <span id="result">30</span>
        </div>
        <div class="w-ful mb-3">
            @foreach ($datas as $item)
                <div class="p-3 mb-3 border rounded shadow">
                    <div class="mt-3 text-end">
                        <button onclick="deleteFn('{{ $item['id'] }}')"
                            class="text-red-600 p-3 border border-red-600 rounded text-center font-bold float-end">Delete!</button>
                    </div>
                    <div>ID : {{ $item['id'] }}</div>
                    @if ($item['type'] == '1')
                        <div class="font-bold text-orange-600"> JOB : {{ $item['name'] }}</div>
                    @elseif($item['type'] == '2')
                        <div class="font-bold text-red-600"> JOB : {{ $item['name'] }}</div>
                    @endif
                    <div>Date : {{ $item['create'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full my-3">
            <button onclick="createServices()"
                class="font-bold flex-grow border rounded border-red-600 p-6 text-red-600">Run
                Create</button>
            <button onclick="clearServices()"
                class="font-bold flex-grow border rounded border-orange-600 p-6 text-orange-600">Run
                Clear</button>
            <button onclick="messageSend()"
                class="font-bold flex-grow border rounded border-green-600 p-6 text-green-600">Message Test</button>
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
        const alert = await Swal.fire({
            icon: 'warning',
            title: 'Confirm : Start Create',
            confirmButtonColor: 'red',
            confirmButtonText: 'Start!',
            showCancelButton: true,
        })
        if (alert.isConfirmed) {
            await axios.post("{{ env('APP_URL') }}/dispatchCreate").then((res) => {
                location.reload();
            })
        }
    }

    async function clearServices() {
        const alert = await Swal.fire({
            icon: 'warning',
            title: 'Confirm : Start Clear',
            confirmButtonColor: 'orange',
            confirmButtonText: 'Start!',
            showCancelButton: true,
        })
        if (alert.isConfirmed) {
            await axios.post("{{ env('APP_URL') }}/dispatchClear").then((res) => {
                location.reload();
            })
        }
    }

    async function deleteFn(id) {
        const alert = await Swal.fire({
            icon: 'warning',
            title: 'Confirm : Delete Create',
            confirmButtonColor: 'red',
            confirmButtonText: 'Delete!',
            showCancelButton: true,
        })
        if (alert.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            await axios.post("{{ env('APP_URL') }}/dispatchDelete", formData).then((res) => {
                location.reload();
            })
        }
    }

    async function messageSend() {
        await axios.post("{{ env('APP_URL') }}/LineMessageCheck").then((res) => {
            Swal.fire({
                icon: 'info',
                title: 'Message send!',
                confirmButtonColor: 'green',
                confirmButtonText: 'confirm',
            })
        })
    }
</script>

</html>
