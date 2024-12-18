@extends('station.layouts.app')
@section('station')
    Lab Patient Counts
@endsection
@section('body')
    <div class="p-6 w-full">
        <div class="text-center font-bold mb-3">Lab Patient Counts Total : {{ count($tasks) }}</div>
        <div class="rounded p-3 mt-3">
            <table class="table w-full border">
                <thead class="border">
                    <th class="p-3 border">#</th>
                    <th class="p-3 border">HN</th>
                    <th class="p-3 border">VN</th>
                    <th class="p-3 border">Name</th>
                </thead>
                <tbody>
                    @foreach ($tasks as $i => $item)
                        <tr class="text-center">
                            <td class="p-3 border">{{ $i + 1 }}</td>
                            <td class="p-3 border">{{ $item->hn }}</td>
                            <td class="p-3 border">{{ $item->vn }}</td>
                            <td class="p-3 border">{{ $item->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                location.reload();
            }, 1000 * 120);
        });
    </script>
@endsection
