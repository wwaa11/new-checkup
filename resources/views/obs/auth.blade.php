@extends("obs.layouts.app")
@section("content")
    <div class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto mb-6 flex h-32 w-32 items-center justify-center rounded-full bg-white shadow-lg">
                    <img class="h-24 w-24 object-contain" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
                </div>
                <h2 class="mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-4xl font-bold text-transparent">
                    B12 OBS
                </h2>
                <p class="text-lg text-slate-600">Observation System Login</p>
            </div>

            <!-- Login Form -->
            <div class="rounded-2xl border border-white/20 bg-white/80 p-8 shadow-xl backdrop-blur-sm">
                <form class="space-y-6" onsubmit="event.preventDefault(); loginFN();">
                    <div class="space-y-4">
                        <!-- User ID Input -->
                        <div class="relative">
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="userid">
                                <i class="fas fa-user mr-2 text-blue-500"></i>รหัสพนักงาน
                            </label>
                            <input class="w-full rounded-lg border border-slate-300 bg-white/50 px-4 py-3 backdrop-blur-sm transition-all duration-200 hover:bg-white/70 focus:border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500" id="userid" type="text" required placeholder="กรอกรหัสพนักงาน">
                        </div>

                        <!-- Password Input -->
                        <div class="relative">
                            <label class="mb-2 block text-sm font-medium text-slate-700" for="password">
                                <i class="fas fa-lock mr-2 text-blue-500"></i>รหัสเข้าคอมพิวเตอร์
                            </label>
                            <input class="w-full rounded-lg border border-slate-300 bg-white/50 px-4 py-3 backdrop-blur-sm transition-all duration-200 hover:bg-white/70 focus:border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500" id="password" type="password" required placeholder="กรอกรหัสผ่าน">
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button class="w-full transform rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 px-4 py-3 font-semibold text-white transition-all duration-200 hover:scale-[1.02] hover:from-blue-600 hover:to-purple-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="submit">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>

                <!-- Additional Info -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        ระบบปลอดภัย - กรุณาใช้รหัสพนักงานของท่าน
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("scripts")
    <script>
        async function loginFN() {
            userid = $('#userid').val();
            password = $('#password').val();
            const formData = new FormData();
            formData.append('userid', userid);
            formData.append('password', password);
            const res = await axios.post("{{ env("APP_URL") }}/authcheck", formData);
            if (res.data.status == 1) {
                window.location = "{{ env("APP_URL") }}/obs";
            } else {
                Swal.fire({
                    title: "Error",
                    text: res.data.text,
                    icon: "error",
                    confirmButtonColor: "blue"
                });
            }
        }
    </script>
@endpush
