@extends('station.layouts.app')
@section('station')
    Log In
@endsection
@section('body')
    <div class="m-auto w-3/4">
        <input id="userid" class="w-full p-3 border border=gray-200 my-3" type="text">
        <input id="password" class="w-full p-3 border border=gray-200 my-3" type="password">
        <button class="w-full rounded p-3 border border-green-400 text-green-400" onclick="loginFN()">Login</button>
    </div>
@endsection
@section('scripts')
    <script>
        async function loginFN() {
            userid = $('#userid').val();
            password = $('#password').val();
            const formData = new FormData();
            formData.append('userid', userid);
            formData.append('password', password);
            const res = await axios.post("/station/auth", formData);
            console.log(res)
            if (res.data.status == 1) {
                window.location = "/station/index";
            } else {
                Swal.fire({
                    title: "Error",
                    text: res.data.text,
                    icon: "error",
                    confirmButtonColor: "blue"
                });
            }
        }
    </script>
@endsection
