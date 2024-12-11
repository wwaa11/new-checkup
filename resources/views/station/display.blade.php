<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display {{ $stations[0]->name }}</title>
    <link rel="shortcut icon" href="{{ asset('images/Logo.ico') }}">
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.slim.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<style>
    .colorChangeText {
        color: #005955;
    }

    .colorChangeBG {
        background-color: #005955
    }
</style>

<body class="font-sans antialiased overflow-hidden" auto>
    <div class="w-full h-screen">
        <div class="w-full max-h-[13%] shadow-lg mb-2 flex font-bold">
            <div class="flex-shrink m-3">
                <img class="h-full p-2" src="{{ asset('images/Side Logo.png') }}" alt="">
            </div>
            <div class="flex-grow text-center m-3">
                <div class="text-3xl colorChangeText">ศูนย์ตรวจสุขภาพ รพ.พระรามเก้า</div>
                <div class="text-s text-red-800">รพ.พระรามเก้า ยินดีต้อนรับ</div>
                <div class="text-s text-red-800">Welcome to Praram 9 Hospital</div>
            </div>
            <div class="flex-shrink flex gap-3 text-3xl p-3 m-3 rounded text-white colorChangeBG">
                <div class="m-auto">
                    <div id="time"></div>
                </div>
                <div class="m-auto flex-grow">
                    <div id="date"></div>
                    <div id="dateen"></div>
                </div>
            </div>
        </div>
        <div class="flex">
            @foreach ($stations as $station)
                <div class="flex-grow">
                    <div class="flex gap-6 mx-6 my-3">
                        @foreach ($station->substations as $sub)
                            <div class="flex-grow text-center colorChangeBG rounded text-white">
                                <div class="p-2">{{ $sub->name }}</div>
                                <div class="p-3 text-6xl" id="{{ $station->id }}_{{ $sub->id }}">-</div>
                                <input type="hidden" id="{{ $station->id }}_{{ $sub->id }}code"
                                    value="{{ $station->code }}">
                                <input type="hidden" id="{{ $station->id }}_{{ $sub->id }}lang">
                                <input type="hidden" id="{{ $station->id }}_{{ $sub->id }}station"
                                    value="{{ preg_replace('/[^0-9]/', '', $sub->name) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="grid grid-cols-3 text-center gap-3 text-white m-3">
            <div class="col-span-2">
                <div class="colorChangeBG rounded p-3 text-3xl">คิวถัดไป / Next Queqe</div>
                <div class="grid grid-rows-4 grid-flow-col" id="process">

                </div>
            </div>
            <div class="col-span-1">
                <div class="colorChangeBG rounded p-3 text-3xl">กรุณาติดต่อแผนก {{ $station->name }}</div>
                <div class="grid grid-cols-2" id="wait">

                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $(document).ready(function() {
        startTime()
        setTimeout(function() {
            getList()
        }, 1000 * 20);
    });

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        };
        return i;
    }

    function startTime() {
        var today = new Date();
        var date = today.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        var dateen = today.getDate() + ' ' + today.toLocaleString('default', {
            month: 'long'
        }) + ' ' + today.getFullYear();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        var t = setTimeout(startTime, 500);
        $('#time').html(h + ":" + m)
        $('#date').html(date)
        $('#dateen').html(dateen)

    }

    async function getList() {
        const callArr = []
        const formData = new FormData();
        formData.append('station', '{{ $stationid }}');
        await axios.post("{{ env('APP_URL') }}/display/list", formData).then((res) => {
            sub = res.data.substation
            for (let index = 0; index < sub.length; index++) {
                if (sub[index]['now'] == null) {
                    display = '-'
                } else {
                    display = sub[index]['now']
                    callArr.push(sub[index]['id'])
                }
                $('#' + sub[index]['id']).html(display)
                $('#' + sub[index]['id'] + 'lang').val(sub[index]['lang'])
            }
            process = res.data.data.process
            processHtml = ''
            for (let index = 0; index < res.data.data.process.length; index++) {
                if (index < 20) {
                    processHtml = processHtml +
                        '<div class="text-6xl colorChangeText p-6 font-bold shadow-lg m-3">' + process[
                            index] +
                        '</div>'
                }
            }
            $('#process').html(processHtml)
            wait = res.data.data.wait
            waitHtml = ''
            for (let index = 0; index < res.data.data.wait.length; index++) {
                if (index < 10) {
                    waitHtml = waitHtml +
                        '<div class="text-6xl colorChangeText p-6 font-bold shadow-lg m-3">' +
                        wait[index] + '</div>'
                }
            }
            $('#wait').html(waitHtml)
        })
        for (let index = 0; index < callArr.length; index++) {
            await playID(callArr[index]);
        }

        setTimeout(function() {
            getList()
        }, 1000 * 5);
    }

    async function playID(id) {
        vn = $('#' + id).html();
        lang = $('#' + id + 'lang').val();
        room = $('#' + id + 'station').val();
        station = $('#' + id + 'code').val();

        isCall = localStorage.getItem(room + vn);
        CallNum = localStorage.getItem(room + vn + 'call');

        if (CallNum == null || CallNum == '1') {
            swal = Swal.fire({
                title: '<div>ขอเชิญหมายเลข</div><div><b style="color: red;font-size: 3em; padding-x: 3em">' +
                    vn + '</b><div><div>ที่ห้อง ' + room + '</div>',
                allowOutsideClick: false,
                showConfirmButton: false,
            })

            localStorage.setItem(room + vn, vn);
            if (CallNum == null) {
                CallNum = 0
            }
            localStorage.setItem(room + vn + 'call', CallNum + 1);

            await playsounds(vn, lang, room, station)
            await new Promise(r => setTimeout(r, 500));
            swal.close()
        }

        return 'success'
    }

    async function playsounds(vn, lang, room, station) {
        vn = vn.split("");
        if (lang == 'th') {
            var audio0_1 = new Audio('{{ asset('sounds/th/0.mp3') }}');
            var audio0_2 = new Audio('{{ asset('sounds/th/0.mp3') }}');
            var audio0_3 = new Audio('{{ asset('sounds/th/0.mp3') }}');
            var audio0_4 = new Audio('{{ asset('sounds/th/0.mp3') }}');
            var audio1 = new Audio('{{ asset('sounds/th/1.mp3') }}');
            var audio2 = new Audio('{{ asset('sounds/th/2.mp3') }}');
            var audio3 = new Audio('{{ asset('sounds/th/3.mp3') }}');
            var audio4 = new Audio('{{ asset('sounds/th/4.mp3') }}');
            var audio5 = new Audio('{{ asset('sounds/th/5.mp3') }}');
            var audio6 = new Audio('{{ asset('sounds/th/6.mp3') }}');
            var audio7 = new Audio('{{ asset('sounds/th/7.mp3') }}');
            var audio8 = new Audio('{{ asset('sounds/th/8.mp3') }}');
            var audio9 = new Audio('{{ asset('sounds/th/9.mp3') }}');
            var audioin = new Audio('{{ asset('sounds/th/in.mp3') }}');
            var audioout = new Audio('{{ asset('sounds/th/out.mp3') }}');

            var room1 = new Audio('{{ asset('sounds/th/1.mp3') }}');
            var room2 = new Audio('{{ asset('sounds/th/2.mp3') }}');
            var room3 = new Audio('{{ asset('sounds/th/3.mp3') }}');
            var room4 = new Audio('{{ asset('sounds/th/4.mp3') }}');
            var room5 = new Audio('{{ asset('sounds/th/5.mp3') }}');

            var staff = new Audio('{{ asset('sounds/th/empty.mp3') }}');
            var vital = new Audio('{{ asset('sounds/th/vs.mp3') }}');
            var blood = new Audio('{{ asset('sounds/th/lab.mp3') }}');
            var ekg = new Audio('{{ asset('sounds/th/ekg.mp3') }}');
            var abi = new Audio('{{ asset('sounds/th/empty.mp3') }}');
            var estecho = new Audio('{{ asset('sounds/th/empty.mp3') }}');
            var xray = new Audio('{{ asset('sounds/th/xray.mp3') }}');
            var mammo = new Audio('{{ asset('sounds/th/mammo.mp3') }}');
            var bone = new Audio('{{ asset('sounds/th/bone.mp3') }}');
            var ultra = new Audio('{{ asset('sounds/th/ultra.mp3') }}');
            var obs = new Audio('{{ asset('sounds/th/obs.mp3') }}');
        } else {
            var audio0_1 = new Audio('{{ asset('sounds/en/0.mp3') }}');
            var audio0_2 = new Audio('{{ asset('sounds/en/0.mp3') }}');
            var audio0_3 = new Audio('{{ asset('sounds/en/0.mp3') }}');
            var audio0_4 = new Audio('{{ asset('sounds/en/0.mp3') }}');
            var audio1 = new Audio('{{ asset('sounds/en/1.mp3') }}');
            var audio2 = new Audio('{{ asset('sounds/en/2.mp3') }}');
            var audio3 = new Audio('{{ asset('sounds/en/3.mp3') }}');
            var audio4 = new Audio('{{ asset('sounds/en/4.mp3') }}');
            var audio5 = new Audio('{{ asset('sounds/en/5.mp3') }}');
            var audio6 = new Audio('{{ asset('sounds/en/6.mp3') }}');
            var audio7 = new Audio('{{ asset('sounds/en/7.mp3') }}');
            var audio8 = new Audio('{{ asset('sounds/en/8.mp3') }}');
            var audio9 = new Audio('{{ asset('sounds/en/9.mp3') }}');
            var audioin = new Audio('{{ asset('sounds/en/in.mp3') }}');
            var audioout = new Audio('{{ asset('sounds/en/empty.mp3') }}');

            var room1 = new Audio('{{ asset('sounds/en/1.mp3') }}');
            var room2 = new Audio('{{ asset('sounds/en/2.mp3') }}');
            var room3 = new Audio('{{ asset('sounds/en/3.mp3') }}');
            var room4 = new Audio('{{ asset('sounds/en/4.mp3') }}');
            var room5 = new Audio('{{ asset('sounds/en/5.mp3') }}');

            var staff = new Audio('{{ asset('sounds/en/empty.mp3') }}');
            var vital = new Audio('{{ asset('sounds/en/vs.mp3') }}');
            var blood = new Audio('{{ asset('sounds/en/lab.mp3') }}');
            var ekg = new Audio('{{ asset('sounds/en/ekg.mp3') }}');
            var abi = new Audio('{{ asset('sounds/en/empty.mp3') }}');
            var estecho = new Audio('{{ asset('sounds/en/empty.mp3') }}');
            var xray = new Audio('{{ asset('sounds/en/xray.mp3') }}');
            var mammo = new Audio('{{ asset('sounds/en/mammo.mp3') }}');
            var bone = new Audio('{{ asset('sounds/en/bone.mp3') }}');
            var ultra = new Audio('{{ asset('sounds/en/ultra.mp3') }}');
            var obs = new Audio('{{ asset('sounds/en/obs.mp3') }}');
        }
        var num1;
        var num2;
        var num3;
        var num4;
        vn.forEach((num, index) => {
            if (index == 0) {
                if (num == 0) {
                    num1 = audio0_1
                };
                if (num == 1) {
                    num1 = audio1
                };
                if (num == 2) {
                    num1 = audio2
                };
                if (num == 3) {
                    num1 = audio3
                };
                if (num == 4) {
                    num1 = audio4
                };
                if (num == 5) {
                    num1 = audio5
                };
                if (num == 6) {
                    num1 = audio6
                };
                if (num == 7) {
                    num1 = audio7
                };
                if (num == 8) {
                    num1 = audio8
                };
                if (num == 9) {
                    num1 = audio9
                };
            }
            if (index == 1) {
                if (num == 0) {
                    num2 = audio0_2
                };
                if (num == 1) {
                    num2 = audio1
                };
                if (num == 2) {
                    num2 = audio2
                };
                if (num == 3) {
                    num2 = audio3
                };
                if (num == 4) {
                    num2 = audio4
                };
                if (num == 5) {
                    num2 = audio5
                };
                if (num == 6) {
                    num2 = audio6
                };
                if (num == 7) {
                    num2 = audio7
                };
                if (num == 8) {
                    num2 = audio8
                };
                if (num == 9) {
                    num2 = audio9
                };
            }
            if (index == 2) {
                if (num == 0) {
                    num3 = audio0_3
                };
                if (num == 1) {
                    num3 = audio1
                };
                if (num == 2) {
                    num3 = audio2
                };
                if (num == 3) {
                    num3 = audio3
                };
                if (num == 4) {
                    num3 = audio4
                };
                if (num == 5) {
                    num3 = audio5
                };
                if (num == 6) {
                    num3 = audio6
                };
                if (num == 7) {
                    num3 = audio7
                };
                if (num == 8) {
                    num3 = audio8
                };
                if (num == 9) {
                    num3 = audio9
                };
            }
            if (index == 3) {
                if (num == 0) {
                    num4 = audio0_4
                };
                if (num == 1) {
                    num4 = audio1
                };
                if (num == 2) {
                    num4 = audio2
                };
                if (num == 3) {
                    num4 = audio3
                };
                if (num == 4) {
                    num4 = audio4
                };
                if (num == 5) {
                    num4 = audio5
                };
                if (num == 6) {
                    num4 = audio6
                };
                if (num == 7) {
                    num4 = audio7
                };
                if (num == 8) {
                    num4 = audio8
                };
                if (num == 9) {
                    num4 = audio9
                };
            }
        });

        var station_name = (function(station) {
            switch (station) {
                case "b12_register":
                    return staff
                case "b12_vitalsign":
                    return vital;
                case "b12_lab":
                    return blood;
                case "b12_ekg":
                    return ekg;
                case "b12_abi":
                    return abi;
                case "b12_echo":
                    return estecho;
                case "b12_chest":
                    return xray;
                case "b12_mammogram":
                    return mammo;
                case "b12_boneden":
                    return bone;
                case "b12_ultrasounds":
                    return ultra;
                case "b12_gny":
                    return obs;
                default:
                    return staff;
            }
        })(station);

        var roomNum = (function(room) {
            switch (room) {
                case '1':
                    return room1;
                case '2':
                    return room2;
                case '3':
                    return room3;
                case '4':
                    return room4;
                case '5':
                    return room5;
            }
        })(room);

        await playAudio(audioin)
        await playAudio(num1)
        await playAudio(num2)
        await playAudio(num3)
        await playAudio(num4)
        await playAudio(station_name)
        await playAudio(roomNum)
        await playAudio(audioout)

        return 'success'
    }

    function playAudio(audio) {
        return new Promise(res => {
            audio.play()
            audio.onended = res
        })
    }
</script>

</html>
