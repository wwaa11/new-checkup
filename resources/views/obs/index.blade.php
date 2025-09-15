@extends("obs.layouts.app")
@section("content")
    <!-- Welcome Header -->
    <div class="mb-8">
        <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mb-2 text-3xl font-bold">B12 Obstetrician-Gynecologist</h1>
                    <p class="text-lg text-blue-100">ระบบจัดการการตรวจสุขภาพ</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-hospital text-6xl text-blue-200"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Registration Card -->
            <a class="group" href="{{ env("APP_URL") }}/obs/registeration">
                <div class="transform rounded-xl border border-slate-200 bg-white p-6 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="rounded-lg bg-green-100 p-3">
                            <i class="fas fa-user-plus text-2xl text-green-600"></i>
                        </div>
                        <i class="fas fa-arrow-right text-slate-400 transition-colors group-hover:text-blue-500"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-slate-800">ลงทะเบียนผู้ป่วย</h3>
                    <p class="text-slate-600">เพิ่มข้อมูลผู้ป่วยใหม่เข้าสู่ระบบ</p>
                    <div class="mt-4 flex items-center font-medium text-green-600">
                        <span>เริ่มลงทะเบียน</span>
                        <i class="fas fa-chevron-right ml-2 text-sm"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Substations -->
    <div>
        <h2 class="mb-6 flex items-center text-2xl font-bold text-slate-800">
            <i class="fas fa-clinic-medical mr-3 text-blue-500"></i>
            ห้องตรวจ
        </h2>

        @if ($stations->substations->count() > 0)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($stations->substations as $index => $substation)
                    <a class="group" href="{{ env("APP_URL") }}/obs/substation/{{ $substation->id }}">
                        <div class="transform rounded-xl border border-slate-200 bg-white p-6 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl">
                            <!-- Station Icon -->
                            <div class="mb-4 flex items-center justify-between">
                                <div class="rounded-lg bg-blue-100 p-3">
                                    <i class="fas fa-stethoscope text-2xl text-blue-600"></i>
                                </div>
                                <div class="rounded-full bg-slate-100 px-3 py-1">
                                    <span class="text-sm font-medium text-slate-600">จุดที่ {{ $index + 1 }}</span>
                                </div>
                            </div>

                            <!-- Station Name -->
                            <h3 class="mb-2 text-lg font-semibold text-slate-800 transition-colors group-hover:text-blue-600">
                                {{ $substation->name }}
                            </h3>

                            <!-- Status Indicator -->
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="mr-2 h-3 w-3 animate-pulse rounded-full bg-green-400"></div>
                                    <span class="text-sm font-medium text-green-600">พร้อมให้บริการ</span>
                                </div>
                                <i class="fas fa-arrow-right text-slate-400 transition-colors group-hover:text-blue-500"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="rounded-xl border border-slate-200 bg-white p-12 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                    <i class="fas fa-clinic-medical text-3xl text-slate-400"></i>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-slate-800">ไม่พบจุดตรวจสุขภาพ</h3>
                <p class="text-slate-600">ยังไม่มีจุดตรวจสุขภาพในระบบ กรุณาติดต่อผู้ดูแลระบบ</p>
            </div>
        @endif
    </div>
@endsection
