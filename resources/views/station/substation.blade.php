@extends('station.layouts.app')
@section('station')
    {{ $substation->name }}
@endsection
@section('body')
    <div class="p-6">
        <div class="flex px-6 gap-3">
            <div class="flex-shrink p-3 font-bold text-2xl text-gray-600">{{ $substation->name }}</div>
            <div class="flex-grow p-3 font-bold text-2xl text-blue-600 text-center shadow bg-gray-100">
                @if ($patient)
                    <div class="grid grid-cols-2 w-full">
                        <div>VN: {{ $patient->vn }}</div>
                        <div>HN: {{ $patient->hn }}</div>
                    </div>
                    <div>{{ $patient->name }}</div>
                @endif
            </div>
            <div class="flex-shrink gap-3 flex">
                <button class="p-3 rounded border w-24 border-blue-500 text-blue-500" type="button"
                    onclick="CallFn()">Call</button>
                <button class="p-3 rounded border w-24 border-amber-500 text-amber-500" type="button"
                    onclick="HoldFn()">Hold</button>
                <button class="p-3 rounded border w-24 border-green-600 text-green-600" type="button"
                    onclick="SuccessFn()">Success</button>
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
            <table class="w-full border border-collapse mt-6 text-center">
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
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            getTask('process')
            getTask('wait')
            getAllTask()
        });
        async function getTask(type) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('type', type);
            await axios.post("{{ env('APP_URL') }}/station/getTask", formData).then((res) => {
                const tasks = res.data.tasks
                setHtml = '';
                var index =
                    tasks.forEach(function(val, i) {
                        setHtml = setHtml + '<div class="grid grid-cols-4 shadow mb-2">';
                        setHtml = setHtml + '<div class="p-2 ">' + val.hn + '</div>'
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
                            '<button class="p-3 flex-grow rounded border border-blue-600 text-blue-600" type="button" onclick="CallFn(\'' +
                            val.hn + '\')">Call</button>'
                        if (type == 'process') {
                            setHtml = setHtml +
                                '<button class="p-3 flex-grow rounded border border-amber-500 text-amber-500" type="button" onclick="HoldFn(\'' +
                                val.hn + '\')">Hold</button>'
                        } else if (type == 'wait') {
                            setHtml = setHtml +
                                '<button class="p-3 flex-grow rounded border border-red-600 text-red-600" type="button" onclick="DeleteFn(\'' +
                                val.hn + '\')">Delete</button>'
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
                // getTask(type)
            }, 1000 * 5);
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
                        setHtml = setHtml + '<td class="p-2 border">' + val.hn + '</td>';
                        setHtml = setHtml + '<td class="p-2 border">' + val.name + '</td>';
                        setHtml = setHtml + '</tr>';
                    })
                $('#alltask').html(setHtml)
            })

            setTimeout(function() {
                getAllTask()
            }, 1000 * 5);
        }

        async function SuccessFn(hn) {
            console.log('Suc : ' + hn)
        }
        async function CallFn(hn) {
            const formData = new FormData();
            formData.append('substation_id', '{{ $substation->id }}');
            formData.append('hn', hn);
            await axios.post("{{ env('APP_URL') }}/station/call", formData).then((res) => {
                location.reload();
            })
        }
        async function HoldFn(hn) {
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
                formData.append('hn', hn);
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
        async function DeleteFn(hn) {
            console.log('del : ' + hn)
        }
    </script>
@endsection
