<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>B12-Obstetrician-Gynecologist</title>
    <link rel="shortcut icon" href="{{ asset("images/Logo.ico") }}">
    <script src="{{ asset("js/sweetalert2@11.js") }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(["resources/css/app.css", "resources/css/design-system.css", "resources/css/custom-animations.css", "resources/js/app.js"])
</head>

<body class="font-inter min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 antialiased">
    @auth
        <nav class="fixed left-0 right-0 top-0 z-50 border-b border-slate-200/50 bg-white/90 shadow-xl backdrop-blur-lg">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <!-- Logo and Brand -->
                    <div class="flex items-center space-x-4">
                        <a class="group flex items-center space-x-4 transition-all duration-300 hover:scale-105" href="{{ route("obs.index") }}">
                            <div class="relative">
                                <img class="h-12 rounded-xl shadow-lg transition-all duration-300 group-hover:shadow-xl" src="{{ asset("images/Side Logo.png") }}" alt="logo">
                                <div class="absolute -inset-1 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 blur transition-opacity duration-300 group-hover:opacity-20"></div>
                            </div>
                            <div class="flex flex-col">
                                <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-2xl font-bold text-transparent">B12-OBS</span>
                                <span class="text-sm font-medium text-slate-600">Obstetrician-Gynecologist</span>
                            </div>
                        </a>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="hidden items-center space-x-8 md:flex">
                        <a class="group flex items-center space-x-2 rounded-lg px-4 py-2 text-slate-700 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600" href="{{ route("obs.index") }}">
                            <i class="fas fa-home transition-colors duration-200 group-hover:text-blue-600"></i>
                            <span class="font-medium">หน้าหลัก</span>
                        </a>
                        <a class="group flex items-center space-x-2 rounded-lg px-4 py-2 text-slate-700 transition-all duration-200 hover:bg-purple-50 hover:text-purple-600" href="{{ route("obs.registeration") }}">
                            <i class="fas fa-user-plus transition-colors duration-200 group-hover:text-purple-600"></i>
                            <span class="font-medium">ลงทะเบียน</span>
                        </a>
                    </div>

                    <!-- User Profile and Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- User Profile -->
                        <div class="hidden items-center space-x-3 rounded-xl bg-gradient-to-r from-slate-100 to-blue-100 px-5 py-3 shadow-sm md:flex">
                            <div class="rounded-full bg-gradient-to-r from-blue-500 to-purple-500 p-2">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                                <span class="text-xs text-slate-600">({{ auth()->user()->userid }})</span>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button class="rounded-lg p-2 text-slate-600 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-800 md:hidden" onclick="toggleMobileMenu()">
                            <i class="fas fa-bars text-xl"></i>
                        </button>

                        <!-- Logout Button -->
                        <button class="group flex items-center space-x-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-5 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:scale-105 hover:from-red-600 hover:to-red-700 hover:shadow-xl" onclick="logoutFn()">
                            <i class="fas fa-sign-out-alt transition-transform duration-200 group-hover:scale-110"></i>
                            <span class="hidden sm:inline">ออกจากระบบ</span>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="hidden border-t border-slate-200 bg-white/95 py-4 md:hidden" id="mobileMenu">
                    <div class="space-y-2">
                        <a class="flex items-center space-x-3 rounded-lg px-4 py-3 text-slate-700 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-600" href="{{ route("obs.index") }}">
                            <i class="fas fa-home"></i>
                            <span class="font-medium">หน้าหลัก</span>
                        </a>
                        <a class="flex items-center space-x-3 rounded-lg px-4 py-3 text-slate-700 transition-colors duration-200 hover:bg-purple-50 hover:text-purple-600" href="{{ route("obs.registeration") }}">
                            <i class="fas fa-user-plus"></i>
                            <span class="font-medium">ลงทะเบียน</span>
                        </a>
                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex items-center space-x-3 px-4 py-2">
                                <div class="rounded-full bg-gradient-to-r from-blue-500 to-purple-500 p-2">
                                    <i class="fas fa-user text-sm text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-600">({{ auth()->user()->userid }})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="h-20"></div>
    @endauth

    <main class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        @yield("content")
    </main>
</body>
<script>
    async function logoutFn() {
        const formData = new FormData();
        const res = await axios.post("{{ env("APP_URL") }}/unauth", formData);
        window.location = "{{ route("obs.auth") }}";
    }

    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        mobileMenu.classList.toggle('hidden');
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuButton = event.target.closest('[onclick="toggleMobileMenu()"]');

        if (!menuButton && !mobileMenu.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    });

    function refreshPage() {
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
</script>
@stack("scripts")

</html>
