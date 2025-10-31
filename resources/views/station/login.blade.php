<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in.</title>
    <link rel="shortcut icon" href="{{ asset("images/Logo.ico") }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js" integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
    @if (file_exists(public_path("build/manifest.json")) || file_exists(public_path("hot")))
        @vite(["resources/css/app.css", "resources/js/app.js"])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="m-auto mt-12 w-1/3">
        <img class="m-auto w-60" src="{{ asset("images/Vertical Logo.png") }}" alt="logo">
        <div class="p-6 text-center text-3xl font-bold">B12 CHECK UP</div>
        <input class="my-3 w-full border border-gray-200 p-3" id="userid" placeholder="รหัสพนักงาน" type="text">
        <input class="my-3 w-full border border-gray-200 p-3" id="password" placeholder="รหัสเข้าคอมพิวเตอร์" type="password">
        <button class="w-full rounded border border-green-400 p-3 text-green-400" onclick="loginFN()">Login</button>
    </div>
</body>
<script>
    async function loginFN() {
        userid = $('#userid').val();
        password = $('#password').val();
        const formData = new FormData();
        formData.append('userid', userid);
        formData.append('password', password);
        const res = await axios.post("{{ env("APP_URL") }}/authcheck", formData);
        if (res.data.status == 1) {
            window.location = "{{ env("APP_URL") }}/station";
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

</html>
