@extends('station.layouts.app')
@section('station')
    Select
@endsection
@section('body')
    <div class="p-6">
        <div class="grid grid-cols-2 gap-3">
            @foreach ($stations as $key => $station)
                <div class="shadow-md p-6 font-bold">
                    <div class="text-center w-full text-2xl p-3">{{ $key }}</div>
                    @if ($key !== 'Register')
                        <a href="{{ env('APP_URL') }}/station/register/{{ $station[0]['station_id'] }}">
                            <div class="p-3 text-center rounded-md border-2 mb-3 border-yellow-300">Register</div>
                        </a>
                    @endif
                    @if ($key == 'ห้องเจาะเลือด')
                        <a href="{{ env('APP_URL') }}/station/labcount">
                            <div class="p-3 text-center rounded-md border-2 mb-3 border-red-300">Patient Counts</div>
                        </a>
                    @endif
                    <div class="grid @if (count($station) > 1) grid-cols-2 @endif gap-3">
                        @foreach ($station as $sub)
                            <a href="{{ env('APP_URL') }}/station/{{ $sub['id'] }}">
                                <div
                                    class="flex-grow border-2 border-green-300 p-3 text-center rounded-md cursor-pointer hover:bg-green-400 hover:text-white">
                                    {{ $sub['name'] }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
