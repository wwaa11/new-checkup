@extends("obs.layouts.app")

@section("content")
    <!-- Page Header -->
    <div class="mb-8">
        <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-blue-600 p-8 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="flex items-center text-4xl font-bold">
                        <i class="fas fa-user-plus mr-4"></i>
                        การลงทะเบียน
                    </h1>
                    <p class="mt-2 text-lg text-emerald-100">ระบบลงทะเบียนผู้ป่วยและจัดการแพทย์ประจำห้อง</p>
                </div>
                <div class="hidden md:block">
                    <div class="rounded-full bg-white/20 p-4">
                        <i class="fas fa-clipboard-list text-5xl text-emerald-200"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Cards -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 xl:grid-cols-3">
        @foreach ($stations->substations as $substation)
            <div class="stagger-item card-hover transform rounded-2xl border border-slate-200 bg-white shadow-lg">
                <!-- Card Header -->
                <div class="rounded-t-2xl bg-gradient-to-r from-slate-50 to-blue-50 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center text-2xl font-bold text-slate-800">
                            <i class="fas fa-clinic-medical mr-3 text-blue-500"></i>
                            {{ $substation->name }}
                        </h2>
                        <div class="pulse-animation rounded-full bg-blue-100 p-2">
                            <i class="fas fa-stethoscope text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Doctor Section -->
                    <div class="mb-8">
                        <div class="mb-4 flex items-center">
                            <i class="fas fa-user-md mr-3 text-green-500"></i>
                            <h3 class="text-lg font-semibold text-slate-800">แพทย์ประจำห้อง</h3>
                        </div>

                        @if ($substation->doctor)
                            <div class="mb-4 rounded-lg bg-green-50 p-4">
                                <div class="flex items-center">
                                    <div class="mr-3 rounded-full bg-green-100 p-2">
                                        <i class="fas fa-user-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-800">{{ $substation->doctor->doctor_code }}</p>
                                        <p class="text-sm text-green-600">{{ $substation->doctor->doctor_name }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-4 rounded-lg bg-amber-50 p-4">
                                <div class="flex items-center">
                                    <div class="mr-3 rounded-full bg-amber-100 p-2">
                                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                                    </div>
                                    <p class="text-amber-800">ยังไม่มีแพทย์ประจำห้อง</p>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-slate-700">รหัสแพทย์</label>
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-id-badge text-slate-400"></i>
                                    </div>
                                    <input class="w-full rounded-lg border border-slate-300 bg-white py-3 pl-10 pr-4 text-slate-900 placeholder-slate-500 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" id="doctor_code_{{ $substation->id }}" type="text" placeholder="กรอกรหัสแพทย์">
                                </div>
                                <button class="flex items-center justify-center rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3 font-semibold text-white transition-all duration-200 hover:scale-105 hover:from-blue-600 hover:to-blue-700 hover:shadow-lg active:scale-95" type="button" onclick="updateDoctor({{ $substation->id }})">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    อัพเดต
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Registration Section -->
                    <div class="border-t border-slate-200 pt-6">
                        <div class="mb-4 flex items-center">
                            <i class="fas fa-user-plus mr-3 text-purple-500"></i>
                            <h3 class="text-lg font-semibold text-slate-800">ลงทะเบียนผู้ป่วย</h3>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-slate-700">หมายเลข HN/VN</label>
                            <div class="flex gap-3">
                                <div class="relative flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-hashtag text-slate-400"></i>
                                    </div>
                                    <input class="w-full rounded-lg border border-slate-300 bg-white py-3 pl-10 pr-4 text-slate-900 placeholder-slate-500 transition-colors focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20" id="hn_vn_{{ $substation->id }}" data-substation-id="{{ $substation->id }}" type="text" placeholder="กรอกหมายเลข HN/VN">
                                </div>
                                <button class="flex items-center justify-center rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-3 font-semibold text-white transition-all duration-200 hover:scale-105 hover:from-purple-600 hover:to-purple-700 hover:shadow-lg active:scale-95" type="button" onclick="registerPatient({{ $substation->id }})">
                                    <i class="fas fa-plus mr-2"></i>
                                    ลงทะเบียน
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push("scripts")
    <script>
        function updateDoctor(substationId) {
            const doctorCode = $('#doctor_code_' + substationId).val();
            if (!doctorCode) {
                Swal.fire({
                    title: "กรุณาใส่ Doctor Code",
                    icon: "warning",
                    confirmButtonColor: "#3085d6",
                });
                return;
            }
            Swal.fire({
                title: "ต้องการอัพเดต Doctor Code ใช่หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "อัพเดต",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ env("APP_URL") }}/obs/registeration/update-doctor", {
                        substation_id: substationId,
                        doctor_code: doctorCode,
                    }).then((response) => {
                        if (response.data.status) {
                            Swal.fire({
                                title: "Updated!",
                                text: "Your file has been updated.",
                                icon: "success",
                            });
                            refreshPage();
                        }
                    }).catch((error) => {
                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error",
                        });
                    });
                }
            });

        }

        function registerPatient(substationId) {
            const hn = $('#hn_vn_' + substationId).val();
            if (!hn) {
                Swal.fire({
                    title: "กรุณาใส่ HN/VN",
                    icon: "warning",
                    confirmButtonColor: "#3085d6",
                });
                return;
            }
            Swal.fire({
                title: "ต้องการลงทะเบียนผู้ป่วย ใช่หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ลงทะเบียน",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ env("APP_URL") }}/obs/registeration/register-patient", {
                        substation_id: substationId,
                        hn: hn,
                    }).then((response) => {
                        if (response.data.status) {
                            Swal.fire({
                                title: "Registered!",
                                text: response.data.message,
                                icon: "success",
                            });
                            $('#hn_vn_' + substationId).val('');
                        }
                    }).catch((error) => {
                        Swal.fire({
                            title: "Error!",
                            text: error.response.data.message,
                            icon: "error",
                        });
                    });
                }
            });
        }
    </script>
@endpush
