@extends("station.layouts.app")
@section("station")
    {{ $substation->name }}
@endsection
@section("body")
    <div class="p-6">
        <div class="flex gap-3 px-6">
            <div class="flex-shrink p-3 pt-4 text-2xl font-bold text-gray-600">{{ $substation->name }}</div>
            <div class="flex flex-grow gap-3">
                <button class="call-btn w-32 rounded border bg-[#008387] py-3 text-white" type="button" onclick="CallFn()">
                    Call New Patient
                </button>
                <div class="flex-grow bg-gray-100 p-3 pt-4 text-2xl font-bold shadow">
                    @if ($patient->enabled)
                        <div class="flex w-full gap-3 text-gray-600">
                            <div class="flex-shrink text-end">VN:
                                <span class="text-red-600" id="vn">{{ $patient->vn }}</span>
                            </div>
                            <div class="flex-grow">Name:
                                <span class="text-blue-600">{{ $patient->name }}</span>
                                ( <span class="text-blue-600"id="hn">{{ $patient->hn }}</span> )
                            </div>
                        </div>
                    @endif
                </div>
                @if ($patient->enabled)
                    <button class="call-btn w-32 rounded border bg-pink-600 py-3 text-white" type="button" onclick="CallSoundFn('{{ $patient->vn }}')">
                        Call Sound
                    </button>
                    <button class="hold-btn w-32 rounded border bg-amber-500 py-3 text-white" type="button" onclick="HoldFn('{{ $patient->vn }}')">
                        Hold
                    </button>
                    <button class="success-btn w-32 rounded border bg-green-600 py-3 text-white" type="button" onclick="SuccessFn('{{ $patient->vn }}')">
                        Success
                    </button>
                @endif
            </div>
        </div>
        <div class="w-full p-6">
            <div class="grid grid-cols-2 gap-3">
                <div class="mt-6 w-full text-center">
                    <div class="rounded bg-[#008387] text-white">
                        <div class="p-3">Waiting...</div>
                        <hr>
                        <div class="grid grid-cols-4 p-3 shadow">
                            <div>HN</div>
                            <div>Name</div>
                            <div>Time</div>
                            <div>Action</div>
                        </div>
                    </div>
                    <div class="flex-col" id="waiting"></div>
                </div>
                <div class="mt-6 w-full text-center">
                    <div class="rounded bg-[#008387] text-white">
                        <div class="p-3">Holding...</div>
                        <hr>
                        <div class="grid grid-cols-4 p-3 shadow">
                            <div>HN</div>
                            <div>Name</div>
                            <div>Time</div>
                            <div>Action</div>
                        </div>
                    </div>
                    <div class="flex-col" id="holding"></div>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="mt-6 w-full flex-grow text-center">
                    <div class="rounded bg-[#008387] text-white">
                        <div class="p-3">Waiting for Register</div>
                        <hr>
                        <div class="grid grid-cols-2 p-3 shadow">
                            <div>HN</div>
                            <div>Name</div>
                        </div>
                    </div>
                    <div id="alltask"></div>
                </div>
                @if ($substation->station->code == "b12_lab")
                    <div class="mt-6 w-full flex-grow text-center">
                        <div class="rounded bg-[#008387] text-white">
                            <div class="p-3">SSP</div>
                            <hr>
                            <div class="grid grid-cols-3 p-3 shadow">
                                <div>HN</div>
                                <div>Name</div>
                                <div>Action</div>
                            </div>
                        </div>
                        <div class="flex-col" id="ssp"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@section("scripts")
    <script>
        $(document).ready(function() {
            getTask('process')
            getTask('wait')
            getAllTask()
            if ('{{ $substation->station->code }}' == 'b12_vitalsign' ||
                '{{ $substation->station->code }}' == 'b12_lab') {
                if ('{{ $patient->enabled }}' == 1) {
                    setTimeout(function() {
                        checksuccess();
                    }, 1000 * 10);
                }
                if ('{{ $substation->station->code }}' == 'b12_lab') {
                    getSSP();
                }
            }
        });

        async function checksuccess() {
            const formData = new FormData();
            formData.append('hn', $('#hn').html());
            formData.append('vn', $('#vn').html());
            formData.append('code', '{{ $substation->station->code }}');
            formData.append('substation_id', '{{ $substation->id }}');
            await axios.post("{{ env("APP_URL") }}/station/checksuccess", formData).then((res) => {
                console.log(res.data.status)
                if (res.data.status == 'success') {
                    location.reload();
                } else {
                    console.log(res)
                    setTimeout(function() {
                        checksuccess();
                    }, 1000 * 5);
                }
            })
        }

        async function getTask(type) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('type', type);
            await axios.post("{{ env("APP_URL") }}/station/getTask", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                tasks.forEach(function(val, i) {
                    setHtml = setHtml + '<div class="grid grid-cols-4 shadow mt-2 ';
                    if (val.Time > 10) {
                        setHtml = setHtml + 'bg-red-100';
                    }
                    setHtml = setHtml + '">';
                    setHtml = setHtml + '<div class="p-2 ">' + val.hn +
                        ' <div class="text-blue-600">(' + val.vn +
                        ') </div>' + '</div>'
                    setHtml = setHtml + '<div class="py-2 text-start">';
                    setHtml = setHtml + '<div>' + val.name + '</div>'
                    if (val.reason !== null) {
                        setHtml = setHtml + '<div class="text-red-600 text-start text-xs">' + val
                            .reason +
                            '</div>'
                    }
                    setHtml = setHtml + '</div>';
                    setHtml = setHtml + '<div class="p-2 ">'
                    setHtml = setHtml + '<div class="">' + val.assignTime + '</div>'
                    setHtml = setHtml + '<div class="text-red-600">( ' + val.Time + ' mins. )</div>'
                    setHtml = setHtml + '</div>'
                    setHtml = setHtml + '<div class="p-2 gap-2 text-center flex">';
                    setHtml = setHtml +
                        '<button class="bg-[#008387] w-32 text-white p-3 flex-grow rounded call-btn" type="button" onclick="CallFn(\'' +
                        val.vn + '\')">Call</button>'
                    if (type == 'process') {
                        setHtml = setHtml +
                            '<button class="bg-amber-500 w-32 text-white flex-grow rounded hold-btn" type="button" onclick="HoldFn(\'' +
                            val.vn + '\')">Hold</button>'
                    } else if (type == 'wait') {
                        setHtml = setHtml +
                            '<button class="bg-red-600 w-32 text-white p-3 flex-grow rounded delete-btn" type="button" onclick="DeleteFn(\'' +
                            val.vn + '\')">Delete</button>'
                    }
                    setHtml = setHtml + '</div>';
                    setHtml = setHtml + '</div>'
                })
                if (type == 'process') {
                    $('#waiting').html(setHtml)
                } else if (type == 'wait') {
                    $('#holding').html(setHtml)
                }
            })

            setTimeout(function() {
                getTask(type)
            }, 1000 * 10);
        }

        async function getSSP() {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            await axios.post("{{ env("APP_URL") }}/station/getSSP", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                tasks.forEach(function(val, i) {
                    setHtml = setHtml + '<div class="grid grid-cols-3 shadow mb-2">';
                    setHtml = setHtml + '<div class="p-2 ">' + val.hn +
                        ' <div class="text-blue-600">(' + val.vn +
                        ') </div>' + '</div>'
                    setHtml = setHtml + '<div class="p-2 ">' + val.name + '</div>'
                    if (val.memo5 == 1) {
                        setHtml = setHtml +
                            '<button class="p-2 m-2 rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white ssp-btn" type="button" onclick="changeSSP(\'' +
                            val.vn + '\')">SSP</button>'
                    } else {
                        setHtml = setHtml +
                            '<button class="p-2 m-2 rounded border bg-gray-200 text-blue-600" type="button" ">Checked</button>'
                    }

                    setHtml = setHtml + '</div>'
                })

                $('#ssp').html(setHtml)
            })

            setTimeout(function() {
                getSSP(type)
            }, 1000 * 60);
        }

        async function changeSSP(vn) {
            const alert = await Swal.fire({
                icon: 'warning',
                title: 'SSP Confirm : ' + vn,
                confirmButtonColor: 'red',
                confirmButtonText: 'Confirm!',
                showCancelButton: true,
            })

            if (alert.isConfirmed) {
                $(`.ssp-btn`).prop('disabled', true);
                $('.ssp-btn').text('Processing...');
                $('.ssp-btn').removeClass('border-blue-600');
                $('.ssp-btn').css('background-color', 'gray');

                const formData = new FormData();
                formData.append('vn', vn);
                await axios.post("{{ env("APP_URL") }}/station/changeSSP", formData).then((res) => {
                    location.reload();
                })
            }
        }

        async function getAllTask() {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            await axios.post("{{ env("APP_URL") }}/station/allTask", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                var index =
                    tasks.forEach(function(val, i) {
                        setHtml = setHtml + '<div class="grid grid-cols-2 shadow mb-2">';
                        setHtml = setHtml + '<div class="p-2 ">' + val.hn +
                            ' <div class="text-blue-600">(' + val.vn +
                            ') </div>' + '</div>'
                        setHtml = setHtml + '<div class="p-2 ">' + val.name + '</div>'
                        setHtml = setHtml + '</div>'
                    })
                $('#alltask').html(setHtml)
            })

            setTimeout(function() {
                getAllTask()
            }, 1000 * 30);
        }

        async function SuccessFn(vn) {
            const alert = await Swal.fire({
                icon: 'info',
                title: 'Success Confirm : ' + vn,
                confirmButtonColor: 'green',
                confirmButtonText: 'SUCCESS!',
                showCancelButton: true,
            })

            if (alert.isConfirmed) {
                $(`.success-btn`).prop('disabled', true);
                $('.success-btn').text('Processing...');
                $('.success-btn').removeClass('bg-green-600');
                $('.success-btn').css('background-color', 'gray');

                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                await axios.post("{{ env("APP_URL") }}/station/success", formData).then((res) => {
                    location.reload();
                })
            }
        }

        async function CallFn(vn) {
            $(`.call-btn`).prop('disabled', true);
            $('.call-btn').text('Calling...');
            $('.call-btn').removeClass('bg-[#008387]');
            $('.call-btn').css('background-color', 'gray');

            console.log('CallFn vn', vn);
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('vn', vn);
            await axios.post("{{ env("APP_URL") }}/station/call", formData).then((res) => {
                location.reload();
            })
        }

        async function CallSoundFn(vn) {
            $(`.call-btn`).prop('disabled', true);
            $('.call-btn').text('Calling...');
            $('.call-btn').removeClass('bg-[#008387]');
            $('.call-btn').css('background-color', 'gray');

            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('vn', vn);
            await axios.post("{{ env("APP_URL") }}/station/callsound", formData).then((res) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Call Sound Sent',
                    showConfirmButton: false,
                    timer: 1500
                })

                $(`.call-btn`).prop('disabled', false);
                $('.call-btn').text('Call Sound');
                $('.call-btn').addClass('bg-[#008387]');
                $('.call-btn').css('background-color', '');
            })
        }

        async function HoldFn(vn) {
            const {
                value: reason
            } = await Swal.fire({
                icon: 'info',
                title: 'โปรดระบุเหตุผล',
                input: 'text',
                inputPlaceholder: 'รายละเอียด',
                confirmButtonColor: 'green'
            })

            if (reason !== '') {
                $(`.hold-btn`).prop('disabled', true);
                $('.hold-btn').text('Holding...');
                $('.hold-btn').removeClass('bg-amber-500');
                $('.hold-btn').css('background-color', 'gray');

                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                formData.append('reason', reason);
                await axios.post("{{ env("APP_URL") }}/station/hold", formData).then((res) => {
                    location.reload();
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณาระบุเหตุผล',
                    showConfirmButton: false
                })
            }

        }

        async function DeleteFn(vn) {
            const alert = await Swal.fire({
                icon: 'warning',
                title: 'Delete Confirm : ' + vn,
                confirmButtonColor: 'red',
                confirmButtonText: 'Delete!',
                showCancelButton: true,
            })

            if (alert.isConfirmed) {
                $(`.delete-btn`).prop('disabled', true);
                $('.delete-btn').text('Deleting...');
                $('.delete-btn').removeClass('bg-red-600');
                $('.delete-btn').css('background-color', 'gray');

                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                await axios.post("{{ env("APP_URL") }}/station/delete", formData).then((res) => {
                    location.reload();
                })
            }
        }
    </script>
@endsection
