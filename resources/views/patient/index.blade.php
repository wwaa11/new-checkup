@extends('patient.layouts.app')
@section('body')
    <div class="lg:p-6">
        <div class="flex">
            <input
                class="focus:outline-2 focus:outline-blue-600  font-bold p-3 text-center placeholder:text-red-400 border w-full rounded-s text-blue-600"
                autocomplete="off" id="input" type="text" placeholder="Check Up Patient Verify: HN, ID card, Phone">
            <button class="hover:bg-blue-600 hover:text-white border font-bold text-blue-600 border-e rounded-e-md p-3"
                type="button" onclick="searchFn()">Search</button>
        </div>
        <div class="mt-5">
            <table class="border table table-auto border-collapse w-full">
                <thead>
                    <tr>
                        <th class="p-3 border">HN</th>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">App No.</th>
                        <th class="p-3 border">Number</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="searchBlock" class="hidden">
                        <td class="p-3 text-center text-blue-600 text-2xl" colspan="4">Searching...</td>
                    </tr>
                </tbody>
                <tbody id="result">
                    <tr class="hidden">
                        <td class="p-3 border">TEST</td>
                        <td class="p-3 border">TEST TEST</td>
                        <td class="p-3 border">99-TEST</td>
                        <td class="p-3 border">
                        <td class="p-3 border"><button
                                class="text-center p-3 w-full font-bold rounded border border-blue-600 text-blue-600"type="button"
                                onclick="requestNumber('TEST')">รับคิว</button>
                        </td>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        async function searchFn() {
            var input = $('#input').val()
            if (input == '') {
                Swal.fire({
                    title: 'Please Input data.',
                    icon: "warning",
                    showConfirmButton: false
                });

                return;
            }
            $('#searchBlock').show()
            $('#result').html('');
            const formData = new FormData();
            formData.append('input', input);
            await axios.post("{{ env('APP_URL') }}/verify", formData, ).then((res) => {
                if (res.data.status == 'success') {
                    $('#searchBlock').hide()
                    html = ''
                    const result = res.data.result
                    result.forEach(element => {
                        html = html + '<tr>'
                        html = html + '<td class="p-3 border text-center">' + element.hn + '</td>'
                        html = html + '<td class="p-3 border text-center">' + element.name + '</td>'
                        html = html + '<td class="p-3 border text-center">' + element.app + '</td>'
                        if (element.type == 1) {
                            html = html + '<td class="p-3 border text-red-600 font-bold text-center">' +
                                element
                                .number +
                                '</td>'
                        } else {
                            html = html +
                                '<td class="p-3 border"><button class="text-center p-3 w-full font-bold rounded border border-blue-600 text-blue-600"type="button" onclick="requestNumber(\''
                            html = html + element.hn + '\',\''
                            html = html + element.name + '\',\''
                            html = html + element.lang + '\',\''
                            html = html + element.app + '\',\''
                            html = html + element.app_time + '\',\''
                            html = html + element.input + '\')'
                            html = html + '">รับคิว</button></td>'
                        }
                        html = html + '</tr>'
                    });
                    $('#result').html(html);
                }
            })
        }

        async function requestNumber(hn, name, lang, app, app_time, input) {
            Swal.fire({
                title: 'Confirm!',
                text: 'ระบคิวสำหรับ ' + hn + ' : ' + name,
                icon: "warning",
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'รับคิว',
                confirmButtonColor: "green",
                cancelButtonText: 'ยกเลิก',
                cancelButtonColor: "#adb5bd"
            }).then(async result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'รอสักครู่..',
                        icon: "warning",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                    const formData = new FormData();
                    formData.append('hn', hn);
                    formData.append('name', name);
                    formData.append('lang', lang);
                    formData.append('app', app);
                    formData.append('app_time', app_time);
                    formData.append('input', input);
                    await axios.post("{{ env('APP_URL') }}/requestNumber", formData).then((
                        res) => {
                        if (res.data.status == 'success') {
                            swal.close()
                            searchFn();
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: res.data.status,
                                icon: "warning",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: true
                            });
                            searchFn();
                        }
                    }).catch(function(error) {
                        Swal.fire({
                            title: 'Error',
                            text: error,
                            icon: "warning",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: true
                        });
                        searchFn();
                    });
                }
            });
        }
    </script>
@endsection
