<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services</title>
    <link rel="shortcut icon" href="{{ asset("images/Logo.ico") }}">
    <script src="{{ asset("js/sweetalert2@11.js") }}"></script>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans antialiased">
    <div class="container mx-auto max-w-7xl p-6">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <div class="mb-4 inline-flex items-center rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-3 shadow-lg">
                <i class="fas fa-cogs mr-3 text-2xl text-white"></i>
                <h1 class="text-2xl font-bold text-white">Service Management</h1>
            </div>
            <div class="mt-6 rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-center">
                    <div class="mr-4 rounded-xl bg-gradient-to-r from-emerald-500 to-green-500 p-4">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <div class="text-left">
                        <h2 class="text-lg font-semibold text-slate-600">Auto Refresh Timer</h2>
                        <div class="flex items-center">
                            <span class="text-3xl font-bold text-slate-800" id="result">30</span>
                            <span class="ml-2 text-lg text-slate-500">seconds</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 h-2 w-full rounded-full bg-slate-200">
                    <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-500 transition-all duration-1000" id="progressBar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <!-- Service Items -->
        <div class="mb-8">
            <h2 class="mb-6 flex items-center text-2xl font-bold text-slate-800">
                <div class="mr-4 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 p-3">
                    <i class="fas fa-list text-white"></i>
                </div>
                Active Services
            </h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($datas as $item)
                    <div class="group transform rounded-2xl border border-slate-200 bg-white p-6 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl">
                        <!-- Service Header -->
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex items-center">
                                @if ($item["type"] == "1")
                                    <div class="mr-3 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 p-3">
                                        <i class="fas fa-briefcase text-white"></i>
                                    </div>
                                @elseif($item["type"] == "2")
                                    <div class="mr-3 rounded-xl bg-gradient-to-r from-red-500 to-rose-500 p-3">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-slate-500">Service ID</div>
                                    <div class="text-lg font-bold text-slate-800">#{{ $item["id"] }}</div>
                                </div>
                            </div>
                            <button class="group/btn rounded-xl bg-gradient-to-r from-red-500 to-rose-500 px-4 py-2 font-semibold text-white shadow-lg transition-all duration-200 hover:scale-105 hover:from-red-600 hover:to-rose-600 hover:shadow-xl" onclick="deleteFn('{{ $item["id"] }}')">
                                <i class="fas fa-trash mr-2 transition-transform duration-200 group-hover/btn:scale-110"></i>
                                Delete
                            </button>
                        </div>

                        <!-- Service Details -->
                        <div class="space-y-3">
                            @if ($item["type"] == "1")
                                <div class="rounded-xl bg-gradient-to-r from-orange-50 to-amber-50 p-4">
                                    <div class="text-sm font-medium text-orange-600">Job Type</div>
                                    <div class="text-lg font-bold text-orange-700">{{ $item["name"] }}</div>
                                </div>
                            @elseif($item["type"] == "2")
                                <div class="rounded-xl bg-gradient-to-r from-red-50 to-rose-50 p-4">
                                    <div class="text-sm font-medium text-red-600">Priority Job</div>
                                    <div class="text-lg font-bold text-red-700">{{ $item["name"] }}</div>
                                </div>
                            @endif
                            <div class="rounded-xl bg-gradient-to-r from-slate-50 to-blue-50 p-4">
                                <div class="text-sm font-medium text-slate-600">Created Date</div>
                                <div class="font-semibold text-slate-800">{{ $item["create"] }}</div>
                                <div class="text-sm text-slate-500">Available at {{ $item["available"] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Action Buttons -->
        <div class="mb-8">
            <h2 class="mb-6 flex items-center text-2xl font-bold text-slate-800">
                <div class="mr-4 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                    <i class="fas fa-play text-white"></i>
                </div>
                Service Actions
            </h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Create Services -->
                <button class="group transform rounded-2xl bg-gradient-to-r from-red-500 to-rose-500 p-8 text-white shadow-xl transition-all duration-300 hover:scale-105 hover:from-red-600 hover:to-rose-600 hover:shadow-2xl" onclick="createServices()">
                    <div class="flex flex-col items-center">
                        <div class="mb-4 rounded-2xl bg-white/20 p-4 transition-transform duration-300 group-hover:scale-110">
                            <i class="fas fa-plus-circle text-4xl"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Run Create</h3>
                        <p class="text-sm opacity-90">Start creating new services</p>
                    </div>
                </button>

                <!-- Clear Services -->
                <button class="group transform rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 p-8 text-white shadow-xl transition-all duration-300 hover:scale-105 hover:from-amber-600 hover:to-orange-600 hover:shadow-2xl" onclick="clearServices()">
                    <div class="flex flex-col items-center">
                        <div class="mb-4 rounded-2xl bg-white/20 p-4 transition-transform duration-300 group-hover:rotate-180 group-hover:scale-110">
                            <i class="fas fa-broom text-4xl"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Run Clear</h3>
                        <p class="text-sm opacity-90">Clear all existing services</p>
                    </div>
                </button>

                <!-- Message Test -->
                <button class="group transform rounded-2xl bg-gradient-to-r from-emerald-500 to-green-500 p-8 text-white shadow-xl transition-all duration-300 hover:scale-105 hover:from-emerald-600 hover:to-green-600 hover:shadow-2xl" onclick="messageSend()">
                    <div class="flex flex-col items-center">
                        <div class="mb-4 rounded-2xl bg-white/20 p-4 transition-transform duration-300 group-hover:scale-110 group-hover:animate-pulse">
                            <i class="fas fa-paper-plane text-4xl"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Message Test</h3>
                        <p class="text-sm opacity-90">Send test message</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</body>
<script>
    var c = setInterval(showclock, 1000)
    var totalSeconds = 30;

    function showclock() {
        var seconds = $('#result').html();
        seconds--;
        $('#result').html(seconds)

        // Update progress bar
        var progressPercentage = (seconds / totalSeconds) * 100;
        $('#progressBar').css('width', progressPercentage + '%');

        // Change color based on remaining time
        if (seconds <= 10) {
            $('#progressBar').removeClass('bg-gradient-to-r from-emerald-500 to-green-500').addClass('bg-gradient-to-r from-red-500 to-rose-500');
        } else if (seconds <= 20) {
            $('#progressBar').removeClass('bg-gradient-to-r from-emerald-500 to-green-500').addClass('bg-gradient-to-r from-amber-500 to-orange-500');
        }

        if (seconds == 0) {
            clearInterval(c);
            // Add loading animation before reload
            $('body').addClass('opacity-50 transition-opacity duration-500');
            setTimeout(() => {
                location.reload();
            }, 500);
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
            await axios.post("{{ env("APP_URL") }}/dispatchCreate").then((res) => {
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
            await axios.post("{{ env("APP_URL") }}/dispatchClear").then((res) => {
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
            await axios.post("{{ env("APP_URL") }}/dispatchDelete", formData).then((res) => {
                location.reload();
            })
        }
    }

    async function messageSend() {
        await axios.post("{{ env("APP_URL") }}/LineMessageCheck").then((res) => {
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
