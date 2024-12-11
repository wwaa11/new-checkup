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
                    <th class="p-3 border">HN</th>
                    <th class="p-3 border">VN</th>
                    <th class="p-3 border">Name</th>
                </thead>
                <tbody>
                    @foreach ($tasks as $item)
                        <tr>
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
