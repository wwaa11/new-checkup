@extends('station.layouts.app')
@section('station')
    Register : {{ $station->name }}
@endsection
@section('body')
    <div class="text-center p-6 text-2xl font-bold">Register {{ $station->name }}</div>
    <div class="flex gap-3 p-6">
        <input class="w-full p-3 border border-blue-600 rounded" type="text" placeholder="HN" id="hn">
        <button class="p-3 border border-blue-600 rounded w-24" onclick="addBtn()">Add</button>
    </div>
@endsection
@section('scripts')
    <script>
        var barcode = "";
        var interval;

        document.addEventListener('keydown', function(evt) {
            if (interval)
                clearInterval(interval);
            if (evt.code == "Enter") {
                if (barcode)
                    handleBarcode(barcode);
                barcode = '';
                return;
            }
            if (evt.code != 'Shift')
                barcode += evt.key;
            interval = setInterval(() => barcode = '', 20);
        });

        async function handleBarcode(scanned_barcode) {
            if (scanned_barcode.length < 9) {
                var hn = scanned_barcode;
            } else {
                var vn = scanned_barcode.substring(52, 56);
                var hn = scanned_barcode.substring(40, 46);
            }

            var result = await register(hn);
            if (result == 'success') {
                Swal.fire({
                    icon: 'info',
                    title: 'Success Add : ' + hn,
                    confirmButtonColor: 'green',
                    confirmButtonText: 'SUCCESS!',
                    showCancelButton: false,
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Add : ' + hn,
                    text: result,
                    confirmButtonColor: 'red',
                    confirmButtonText: 'confirm!',
                    showCancelButton: false,
                })
            }
        }

        async function addBtn() {
            var hn = $('#hn').val()
            if (hn == '') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Error please input HN',
                    confirmButtonColor: 'red',
                    confirmButtonText: 'Confirm!',
                    showCancelButton: false,
                })
            }
            var result = await register(hn);

            if (result[0] == 'success') {
                Swal.fire({
                    icon: 'info',
                    title: 'Success!',
                    html: '<div class="text-2xl text-blue-600">HN : ' + hn + '</div>' +
                        '<div class="text-2xl text-blue-600">Name : ' + result[1].name + '</div>',
                    confirmButtonColor: 'green',
                    confirmButtonText: 'Confirm!',
                    showCancelButton: false,
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Add : ' + hn,
                    text: result[1],
                    confirmButtonColor: 'red',
                    confirmButtonText: 'Confirm!',
                    showCancelButton: false,
                })
            }

        }

        async function register(hn) {
            const formData = new FormData();
            formData.append('hn', hn);
            formData.append('station_id', '{{ $station->id }}');
            result = await axios.post("{{ env('APP_URL') }}/station/register", formData).then((res) => {
                if (res.data.status == 'success') {
                    return ['success', res.data.patient]
                }
                return ['err', res.data.text]
            })

            return result
        }
    </script>
@endsection
