<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1"
          name="viewport" />
    <meta content="{{ csrf_token() }}"
          name="csrf-token" />

    <title>{{ $title }} | {{ config('app.name') }}</title>

    <link href="{{ asset('img/favicon.ico') }}"
          rel="icon"
          type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
          rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          rel="stylesheet" />
    @vite('resources/scss/style.scss')
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
