<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <meta content="{{ csrf_token() }}"
          name="csrf-token" />

    <title> {{ config('app.name') }}</title>

    <link href="{{ asset('favicon.ico') }}"
          rel="icon"
          type="image/x-icon">

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
          rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet" />

    @vite('resources/scss/style.scss')
</head>

<body>

    <div class="vh-100 d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">
                    <div class="text-center mb-3">
                        <img alt="Logo"
                             class="w-75"
                             src="{{ asset('images/logo.png') }}">
                    </div>
                    <a class="btn btn-primary btn-block btn-lg mb-2"
                       href="{{ route('auth.login') }}">Go to Back Office <i
                           class="bi bi-box-arrow-in-right ml-2"></i></a>
                    <a class="btn btn-outline-primary btn-block btn-lg"
                       href="{{ config('shop.front_url') }}">
                        Go to Shop <i class="bi bi-box-arrow-in-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
