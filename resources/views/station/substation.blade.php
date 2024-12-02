@extends('station.layouts.app')
@section('station')
    {{ $substation->name }}
@endsection
@section('body')
    <div class="p-6">
        <div class="flex px-6 gap-3">
            <div class="flex-shrink p-3 font-bold text-2xl text-gray-600">{{ $substation->name }}</div>
            <div class="flex-grow p-3 font-bold text-2xl shadow bg-gray-100">
                @if ($patient->enabled)
                    <div class="grid grid-cols-2 w-full gap-3 text-gray-600">
                        <div class="text-end">VN: <span class="text-red-600" id="vn">{{ $patient->vn }}</span></div>
                        <div>Name: <span class="text-blue-600">{{ $patient->name }}</span> ( <span class="text-blue-600"
                                id="hn">{{ $patient->hn }}</span> )</div>
                    </div>
                @endif
            </div>
            <div class="flex-shrink gap-3 flex">
                <button class="p-3 rounded border w-24 border-blue-500 text-blue-500 hover:bg-blue-600 hover:text-white"
                    type="button" onclick="CallFn()">
                    Call
                </button>
                @if ($patient->enabled)
                    <button
                        class="p-3 rounded border w-24 border-amber-500 text-amber-500 hover:bg-amber-600 hover:text-white"
                        type="button" onclick="HoldFn('{{ $patient->vn }}')">
                        Hold
                    </button>
                    <button
                        class="p-3 rounded border w-24 border-green-600 text-green-600 hover:bg-green-600 hover:text-white"
                        type="button" onclick="SuccessFn('{{ $patient->vn }}')">
                        Success
                    </button>
                @endif
            </div>
        </div>
        <div class="w-full p-6">
            <div class="grid grid-cols-2 gap-3">
                <div class="w-full mt-6 text-center">
                    <div class="bg-blue-600 text-white rounded">
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
                <div class="w-full mt-6 text-center">
                    <div class="bg-amber-300 rounded">
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
                <table class="w-full border border-collapse mt-6 text-center flex-grow">
                    <thead>
                        <tr>
                            <th class="p-3 bg-gray-400" colspan="3">All Patient</th>
                        </tr>
                        <tr>
                            <th class="p-2 border"></th>
                            <th class="p-2 border">HN</th>
                            <th class="p-2 border">Name</th>
                        </tr>
                    </thead>
                    <tbody id="alltask">
                        <tr>
                            <td class="p-2 border"></td>
                            <td class="p-2 border">Please Wait...</td>
                            <td class="p-2 border">Please Wait...</td>
                        </tr>
                    </tbody>
                </table>
                @if ($substation->station->code == 'b12_lab')
                    <div class="w-full mt-6 text-center flex-grow">
                        <div class="rounded">
                            <div class="p-3 bg-red-600 text-white ">SSP</div>
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
@section('scripts')
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
                    }, 1000 * 30);
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
            await axios.post("{{ env('APP_URL') }}/station/checksuccess", formData).then((res) => {
                console.log(res.data.status)
                if (res.data.status == 'success') {
                    location.reload();
                } else {
                    console.log(res)
                    setTimeout(function() {
                        checksuccess();
                    }, 1000 * 10);
                }
            })
        }

        async function getTask(type) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('type', type);
            await axios.post("{{ env('APP_URL') }}/station/getTask", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                tasks.forEach(function(val, i) {
                    setHtml = setHtml + '<div class="grid grid-cols-4 shadow mb-2">';
                    setHtml = setHtml + '<div class="p-2 ">' + val.hn +
                        ' <div class="text-blue-600">(' + val.vn +
                        ') </div>' + '</div>'
                    setHtml = setHtml + '<div class="p-2 ">';
                    setHtml = setHtml + '<div>' + val.name + '</div>'
                    if (val.reason !== null) {
                        setHtml = setHtml + '<div class="text-red-600 text-start text-xs">' + val
                            .reason +
                            '</div>'
                    }
                    setHtml = setHtml + '</div>';
                    setHtml = setHtml + '<div class="p-2 ">'
                    setHtml = setHtml + '<div class="">' + val.assign + '</div>'
                    setHtml = setHtml + '<div class="text-red-600">( ' + val.Time + ' mins. )</div>'
                    setHtml = setHtml + '</div>'
                    setHtml = setHtml + '<div class="p-2 gap-2 text-center flex">';
                    setHtml = setHtml +
                        '<button class="p-3 flex-grow rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white" type="button" onclick="CallFn(\'' +
                        val.vn + '\')">Call</button>'
                    if (type == 'process') {
                        setHtml = setHtml +
                            '<button class="p-3 flex-grow rounded border border-amber-500 text-amber-500 hover:bg-amber-600 hover:text-white" type="button" onclick="HoldFn(\'' +
                            val.vn + '\')">Hold</button>'
                    } else if (type == 'wait') {
                        setHtml = setHtml +
                            '<button class="p-3 flex-grow rounded border border-red-600 text-red-600 hover:bg-red-600 hover:text-white" type="button" onclick="DeleteFn(\'' +
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
            await axios.post("{{ env('APP_URL') }}/station/getSSP", formData).then((res) => {
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
                            '<button class="p-2 m-2 rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white" type="button" onclick="changeSSP(\'' +
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
            }, 1000 * 30);
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
                const formData = new FormData();
                formData.append('vn', vn);
                await axios.post("{{ env('APP_URL') }}/station/changeSSP", formData).then((res) => {
                    location.reload();
                })
            }
        }

        async function getAllTask() {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            await axios.post("{{ env('APP_URL') }}/station/allTask", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                var index =
                    tasks.forEach(function(val, i) {
                        setHtml = setHtml + '<tr>';
                        setHtml = setHtml + '<td class="p-2 border">' + (i + 1) + '</td>';
                        setHtml = setHtml + '<td class="p-2 border">' + val.hn +
                            '<div class="text-blue-600">(' + val.vn + ')</div>' + '</td>';

                        setHtml = setHtml + '<td class="p-2 border">' + val.name + '</td>';
                        setHtml = setHtml + '</tr>';
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
                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                await axios.post("{{ env('APP_URL') }}/station/success", formData).then((res) => {
                    location.reload();
                })
            }
        }

        async function CallFn(vn) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('vn', vn);
            await axios.post("{{ env('APP_URL') }}/station/call", formData).then((res) => {
                location.reload();
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
                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                formData.append('reason', reason);
                await axios.post("{{ env('APP_URL') }}/station/hold", formData).then((res) => {
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
                const formData = new FormData();
                formData.append('substation_id', '{{ $substation->id }}');
                formData.append('vn', vn);
                await axios.post("{{ env('APP_URL') }}/station/delete", formData).then((res) => {
                    location.reload();
                })
            }
        }
    </script>
@endsection
