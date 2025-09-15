@extends("obs.layouts.app")
@section("content")
    <!-- Station Header -->
    <div class="mb-8">
        <div class="rounded-2xl bg-gradient-to-r from-purple-600 to-blue-600 p-8 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="mb-2 flex items-center">
                        <a class="mr-3 text-purple-200 transition-colors hover:text-white" href="{{ route("obs.index") }}">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <h1 class="flex items-center text-3xl font-bold">
                            <i class="fas fa-clinic-medical mr-3"></i>
                            {{ $substation->name }}
                        </h1>
                    </div>
                    <p class="text-lg text-purple-100">จุดตรวจสุขภาพ - ระบบจัดการผู้ป่วย</p>
                </div>
                <div class="hidden md:block">
                    <div class="rounded-full bg-white/20 p-4">
                        <i class="fas fa-stethoscope text-4xl text-purple-200"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Station Status Cards -->
    <div class="flex-3 mb-8 flex gap-6">
        @if ($substation->now != null)
            <!-- Current Queue -->
            <div class="flex-2 group transform rounded-2xl border border-slate-200 bg-white p-6 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <div class="rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 p-4 transition-all duration-300 group-hover:from-blue-200 group-hover:to-blue-300">
                        <i class="fas fa-user text-3xl text-blue-600"></i>
                    </div>
                    <div class="text-right">
                        <button class="group w-full cursor-pointer rounded-2xl px-6 py-5 text-green-500 transition-all duration-300 hover:scale-105" onclick="callPatientAgain()">
                            <div class="flex items-center justify-center">
                                <div class="text-left">
                                    <div class="text-lg font-bold">เรียกผู้ป่วยอีกครั้ง</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-slate-800">Patient Name : {{ $substation->patientNow->name }}</h3>
                <p class="text-sm text-slate-600" id="patient-info">
                    HN : {{ $substation->patientNow->hn }} VN : {{ $substation->patientNow->vn }}
                </p>
                <div class="mt-4 h-2 w-full rounded-full bg-blue-100">
                    <div class="h-2 w-full rounded-full bg-gradient-to-r from-blue-400 to-blue-600"></div>
                </div>
            </div>
        @endif

        <!-- Currently Serving -->
        <div class="group flex-1 transform rounded-2xl border border-slate-200 bg-white p-6 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <div class="rounded-xl bg-gradient-to-br from-green-100 to-green-200 p-4 transition-all duration-300 group-hover:from-green-200 group-hover:to-green-300">
                    <i class="fas fa-user-md text-3xl text-green-600"></i>
                </div>
                <div class="text-right">
                    @if ($substation->now != null)
                        <span class="block text-3xl font-bold text-green-600">
                            {{ $substation->now }}
                        </span>
                        <div class="mt-1 flex items-center text-sm text-green-500">
                            <div class="mr-1 h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                            <span>กำลังตรวจ </span>
                        </div>
                    @endif
                </div>
            </div>
            <h3 class="mb-2 text-xl font-semibold text-slate-800">Doctor : @if ($substation->doctor != null)
                    {{ $substation->doctor->doctor_name }}
                @else
                    ไม่ระบุ
                @endif
            </h3>
            <p class="text-sm text-slate-600">หมายเลขคิวที่กำลังให้บริการ</p>
            <div class="mt-4 h-2 w-full rounded-full bg-green-100">
                <div class="h-2 w-full rounded-full bg-gradient-to-r from-green-400 to-green-600"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Queue Wait Management -->
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <!-- Queue Header -->
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-blue-50 to-indigo-50 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center text-2xl font-bold text-slate-800">
                            <div class="mr-4 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 p-3">
                                <i class="fas fa-list-ol text-white"></i>
                            </div>
                            รายการคิว
                        </h2>
                    </div>
                </div>

                <!-- Queue List -->
                <div class="p-6">
                    <div class="space-y-4" id="queueWaitList">
                        <!-- Queue items will be loaded here -->
                        <div class="py-12 text-center text-slate-500">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-slate-100 to-blue-100">
                                <i class="fas fa-clock text-4xl text-slate-400"></i>
                            </div>
                            <h3 class="mb-2 text-xl font-semibold text-slate-600">ไม่มีผู้ป่วยในคิว</h3>
                            <p class="text-slate-500">คิวจะแสดงที่นี่เมื่อมีผู้ป่วยลงทะเบียน</p>
                            <div class="mt-6 flex justify-center">
                                <div class="rounded-full bg-gradient-to-r from-blue-100 to-purple-100 px-4 py-2">
                                    <span class="text-sm font-medium text-slate-600">รอการลงทะเบียน...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <!-- Control Header -->
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-green-50 to-emerald-50 px-6 py-5">
                    <h2 class="flex items-center text-2xl font-bold text-slate-800">
                        <div class="mr-4 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 p-3">
                            <i class="fas fa-cogs text-white"></i>
                        </div>
                        ควบคุมระบบ
                    </h2>
                </div>
                <!-- Control Actions -->
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Call Next Patient -->
                        <button class="group w-full rounded-2xl bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-5 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-green-600 hover:to-emerald-600 hover:shadow-xl" onclick="callPatient('auto')">
                            <div class="flex items-center justify-center">
                                <div class="mr-4 rounded-xl bg-white/20 p-3 transition-transform duration-300 group-hover:scale-110">
                                    <i class="fas fa-user-plus text-2xl"></i>
                                </div>
                                <div class="cursor-pointer text-left">
                                    <div class="text-lg font-bold">เรียกผู้ป่วยคนต่อไป</div>
                                    <div class="text-sm opacity-90">เรียกผู้ป่วยในคิวถัดไป</div>
                                </div>
                            </div>
                        </button>

                        <!-- Skip Current Patient -->
                        <button class="group w-full rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-amber-600 hover:to-orange-600 hover:shadow-xl" onclick="skipPatient('auto')">
                            <div class="flex items-center justify-center">
                                <div class="mr-4 rounded-xl bg-white/20 p-3 transition-transform duration-300 group-hover:scale-110">
                                    <i class="fas fa-forward text-2xl"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-lg font-bold">ข้ามผู้ป่วยปัจจุบัน</div>
                                    <div class="text-sm opacity-90">ข้ามไปผู้ป่วยคนต่อไป</div>
                                </div>
                            </div>
                        </button>

                        <!-- Complete Checkup -->
                        <button class="group w-full rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-5 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-blue-600 hover:to-indigo-600 hover:shadow-xl" onclick="completePatient()">
                            <div class="flex items-center justify-center">
                                <div class="mr-4 rounded-xl bg-white/20 p-3 transition-transform duration-300 group-hover:scale-110">
                                    <i class="fas fa-check text-2xl"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-lg font-bold">ตรวจเสร็จสิ้น</div>
                                    <div class="text-sm opacity-90">บันทึกการตรวจเสร็จสิ้น</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Hold Management -->
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <!-- Queue Header -->
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-blue-50 to-indigo-50 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center text-2xl font-bold text-slate-800">
                            <div class="mr-4 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 p-3">
                                <i class="fas fa-list-ol text-white"></i>
                            </div>
                            รายการคิวที่ถูกพัก
                        </h2>
                    </div>
                </div>

                <!-- Queue Hold List -->
                <div class="p-6">
                    <div class="space-y-4" id="queueHoldList">
                        <!-- Queue items will be loaded here -->
                        <div class="py-12 text-center text-slate-500">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-slate-100 to-blue-100">
                                <i class="fas fa-clock text-4xl text-slate-400"></i>
                            </div>
                            <h3 class="mb-2 text-xl font-semibold text-slate-600">ไม่มีผู้ป่วยในคิว</h3>
                            <p class="text-slate-500">คิวจะแสดงที่นี่เมื่อมีผู้ป่วยลงทะเบียน</p>
                            <div class="mt-6 flex justify-center">
                                <div class="rounded-full bg-gradient-to-r from-blue-100 to-purple-100 px-4 py-2">
                                    <span class="text-sm font-medium text-slate-600">รอการลงทะเบียน...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            getTask('process')
            getTask('wait')
        });

        async function getTask(type) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('type', type);
            await axios.post("{{ route("obs.substation.getTask") }}", formData).then((res) => {
                const tasks = res.data.tasks
                if (tasks.length == 0) {
                    return
                }
                setHtml = '';
                tasks.forEach(function(val, i) {
                    setHtml += `
                        <div class="flex items-center justify-between border-b border-slate-200 py-4">
                            <div class="flex items-center">
                                <div class="mr-4  p-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-bold">${val.vn} : ${val.patient.name}</div>
                                    <div class="text-sm opacity-90">${val.waitingTime} นาที</div>`
                    if (type == 'wait' && val.memo4 !== null) {
                        setHtml += `<div class="text-sm text-red-500 fw-bold">${val.memo4}</div>`
                    }
                    setHtml += ` </div>
                            </div>
                            <div class="text-right flex justify-between gap-6">`;
                    if (type == 'process') {
                        setHtml += `<button onclick="skipPatient('${val.id}')" class="flex-1 rounded-2xl bg-gradient-to-r from-red-500 to-orange-500 px-6 py-3 text-white shadow-lg">ข้าม</button>`
                    } else {
                        setHtml += `<button onclick="cancelPatient('${val.id}')" class="flex-1 rounded-2xl bg-gradient-to-r from-red-500 to-red-700 px-6 py-3 text-white shadow-lg">ยกเลิก</button>`
                    }
                    setHtml += `    <button onclick="callPatient('${val.id}')" class="flex-1 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-3 text-white shadow-lg">เรียก</button>
                            </div>
                        </div>
                    `
                })

                if (type == 'process') {
                    $('#queueWaitList').html(setHtml)
                } else if (type == 'wait') {
                    $('#queueHoldList').html(setHtml)
                }
            })

            setTimeout(function() {
                getTask(type)
            }, 1000 * 10);
        }

        async function skipPatient(id) {
            Swal.fire({
                title: "ต้องการข้ามผู้ป่วยนี้หรือไม่?",
                text: "You won't be able to revert this!",
                input: 'textarea',
                inputPlaceholder: 'Enter reason...',
                inputAttributes: {
                    'autocapitalize': 'off'
                },
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "gray",
                cancelButtonColor: "#d33",
                confirmButtonText: "ยืนยันการข้ามผู้ป่วย"
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: "Loading...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('id', id);
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('reason', result.value);
                await axios.post("{{ route("obs.substation.skipPatient") }}", formData).then((res) => {
                    if (res.data.status == 'success') {
                        Swal.fire({
                            title: "Success!",
                            text: res.data.message,
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
            });
        }

        async function callPatient(id) {
            Swal.fire({
                title: "ต้องการเรียกผู้ป่วยนี้หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#00bc7d",
                cancelButtonColor: "gray",
                confirmButtonText: "ยืนยันการเรียกผู้ป่วย"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Loading...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('substation_id', '{{ $substation->id }}');
                    await axios.post("{{ route("obs.substation.callPatient") }}", formData).then((res) => {
                        if (res.data.status == 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: res.data.message,
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

        async function cancelPatient(id) {
            Swal.fire({
                title: "ต้องการยกเลิกผู้ป่วยนี้หรือไม่?",
                icon: "warning",
                text: "You won't be able to revert this!",
                input: 'textarea',
                inputPlaceholder: 'Enter reason...',
                inputAttributes: {
                    'autocapitalize': 'off'
                },
                showCancelButton: true,
                confirmButtonColor: "gray",
                cancelButtonColor: "#d33",
                confirmButtonText: "ยืนยันการยกเลิกผู้ป่วย"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Loading...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('substation_id', '{{ $substation->id }}');
                    formData.append('reason', result.value);
                    await axios.post("{{ route("obs.substation.cancelPatient") }}", formData).then((res) => {
                        if (res.data.status == 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: res.data.message,
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

        async function callPatientAgain() {
            Swal.fire({
                title: "ต้องการเรียกผู้ป่วยอีกครั้งหรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#00bc7d",
                cancelButtonColor: "gray",
                confirmButtonText: "เรียกผู้ป่วยอีกครั้ง"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Loading...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('substation_id', '{{ $substation->id }}');
                    await axios.post("{{ route("obs.substation.callAgainPatient") }}", formData).then((res) => {
                        if (res.data.status == 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: res.data.message,
                                icon: "success",
                                timer: 1000,
                            });
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

        async function completePatient() {
            Swal.fire({
                title: "ต้องการตรวจเสร็จสิ้นผู้ป่วยนี้หรือไม่?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#00bc7d",
                cancelButtonColor: "gray",
                confirmButtonText: "ยืนยันการตรวจเสร็จสิ้นผู้ป่วย"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Loading...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('substation_id', '{{ $substation->id }}');
                    await axios.post("{{ route("obs.substation.successPatient") }}", formData).then((res) => {
                        if (res.data.status == 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: res.data.message,
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
    </script>
@endpush
