@extends("obs.layouts.app")
@section("content")
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-32 w-32 bg-white rounded-full shadow-lg flex items-center justify-center mb-6">
                    <img class="h-24 w-24 object-contain" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
                </div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    B12 OBS
                </h2>
                <p class="text-slate-600 text-lg">Observation System Login</p>
            </div>

            <!-- Login Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-white/20">
                <form class="space-y-6" onsubmit="event.preventDefault(); loginFN();">
                    <div class="space-y-4">
                        <!-- User ID Input -->
                        <div class="relative">
                            <label for="userid" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-user mr-2 text-blue-500"></i>รหัสพนักงาน
                            </label>
                            <input 
                                id="userid" 
                                type="text" 
                                required 
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/50 backdrop-blur-sm hover:bg-white/70 focus:bg-white" 
                                placeholder="กรอกรหัสพนักงาน"
                            >
                        </div>

                        <!-- Password Input -->
                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-500"></i>รหัสเข้าคอมพิวเตอร์
                            </label>
                            <input 
                                id="password" 
                                type="password" 
                                required 
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/50 backdrop-blur-sm hover:bg-white/70 focus:bg-white" 
                                placeholder="กรอกรหัสผ่าน"
                            >
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
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
                window.location = "{{ route("obs.index") }}";
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
