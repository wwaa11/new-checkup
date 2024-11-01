@extends('station.layouts.app')
@section('station')
    Select
@endsection
@section('body')
    <div class="p-6">
        <div class="grid grid-cols-3 gap-3">
            @foreach ($stations as $key => $station)
                <div class="shadow-md p-6 font-bold">
                    <div class="text-center w-full text-2xl p-3">{{ $key }}</div>
                    <div class="p-3 text-center rounded-md border-2 mb-3 border-yellow-300">Register</div>
                    <div class="grid @if (count($station) > 1) grid-cols-2 @endif gap-3">
                        @foreach ($station as $sub)
                            <div class="flex-grow border-2 border-green-300 p-3 text-center rounded-md">
                                {{ $sub['name'] }}
                            </div>
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
