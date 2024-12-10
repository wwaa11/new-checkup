@extends('station.layouts.app')
@section('station')
    Lab Patient Counts
@endsection
@section('body')
    <div class="p-6 w-full">
        <div class="text-center font-bold mb-3">Lab Patient Counts</div>
        <div class="rounded bg-gray-50 p-3 mt-3">
            <div>{{ $tasks->links() }}</div>
            @foreach ($tasks as $item)
                <div>{{ $item->hn }}</div>
                <div>{{ $item->name }}</div>
                <div>{{ $item->vn }}</div>
            @endforeach
        </div>
    </div>
@endsection
