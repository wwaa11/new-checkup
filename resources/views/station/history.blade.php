@extends('station.layouts.app')
@section('station')
    Check history
@endsection
@section('body')
    <div class="p-6 w-full">
        <div class="text-center font-bold mb-3">Check History</div>
        <div class="flex gap-6">
            <input type="date" class="p-3 border border-blue-600 rounded" value="{{ date('Y-m-d') }}" id="date">
            <input class="flex-grow p-3 border border-blue-600 rounded" type="text" placeholder="HN, VN" id="search">
            <button class="p-3 border border-blue-600 rounded" onclick="search()">Search</button>
        </div>
        <div class="rounded bg-gray-50 p-3 mt-3">
            @if ($patient !== null)
                <div class="text-center font-bold text-xl mb-3">{{ $patient->date }} Name :
                    {{ $patient->name }} HN :
                    {{ $patient->hn }} VN :
                    {{ $patient->vn }}</div>
                <div>
                    @if (count($patient->logs) > 0)
                        @foreach ($patient->logs as $log)
                            <div class="flex shaodw mb-3 font-bold hover:bg-blue-100">
                                <div class="p-3 flex-grow">{{ $log->text }}</div>
                                <div class="p-3">{{ $log->created_at->format('H:i') }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @else
                <div class="text-center font-bold text-xl mb-3">ไม่พบข้อมูล</div>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function search() {
            date = $('#date').val()
            input = $('#search').val()
            if (input !== undefined) {
                location.replace("{{ env('APP_URL') }}/history?input=" + input + "&date=" + date)
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Error please input HN',
                    confirmButtonColor: 'red',
                    confirmButtonText: 'Confirm!',
                    showCancelButton: false,
                })
            }
        }
    </script>
@endsection
