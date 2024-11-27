@extends('patient.layouts.app')
@section('body')
    <div class="p-6">
        <div>HN {{ $data['hn'] }}</div>
        <div>HN {{ $data['name'] }}</div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
