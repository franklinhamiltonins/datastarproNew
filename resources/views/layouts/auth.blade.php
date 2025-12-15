<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | @yield('pagetitle')</title>
    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ mix('css/main.css') }}" rel="stylesheet">
    <link href="{{ mix('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css"
        integrity="sha512-q3eWabyZPc1XTCmF+8/LuE1ozpg5xxn7iO89yfSOd5/oKvyqLngoNGsx8jq92Y8eXJ/IRxQbEC+FGSYxtk2oiw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('styles')
</head>

<body class="login-page">
    <div class="wrapper" id="app">
        <main>
            @yield('content')
        </main>
    </div>
    <footer class="main-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <div class="text-center text-lg-left mb-2 mb-lg-0">
                        <p class="mb-0">Copyright © {{ now()->year }} - <strong>{{env('APP_NAME')}}</strong>. All rights reserved.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="f-nav text-center mb-2 mb-lg-0">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item"><a href="{{route('privacy-policy')}}">Privacy Policy</a></li>
                            <li class="list-inline-item"><a href="{{route('terms-condition')}}">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="text-center text-lg-right">
                        <b>Version</b> 1.0.0
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>

</html>