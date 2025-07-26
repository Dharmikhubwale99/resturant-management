<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icon/hubwalelogopng.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .loader-logo {
            width: 200px;
            height: 80px;
            margin-bottom: 20px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
            100% { transform: scale(1); opacity: 1; }
        }

        body:not(.loaded) main,
        body:not(.loaded) footer {
            opacity: 0;
            visibility: hidden;
        }

        body.loaded main,
        body.loaded footer {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="page-loader" id="pageLoader">
        <img src="{{ asset('icon/Jobhubwale_Final_01.png') }}" alt="Logo" class="loader-logo">
    </div>

    @include('components.layouts.auth.navbar')

    <main class="">
        <div class="min-h-[calc(100vh-4rem-5rem)]">

            {{ $slot }}
        </div>
    </main>

    @include('components.layouts.auth.footer')

    @livewireScripts
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pageLoader = document.getElementById('pageLoader');


            const logo = document.querySelector('.loader-logo');
            if (logo.complete) {
                initLoader();
            } else {
                logo.addEventListener('load', initLoader);
                logo.addEventListener('error', initLoader);
            }

            function initLoader() {

                window.addEventListener('load', function() {
                    hideLoader();
                });


                setTimeout(function() {
                    if (document.body.classList.contains('loaded')) return;
                    hideLoader();
                }, 5000);
            }

            function hideLoader() {
                pageLoader.classList.add('fade-out');
                document.body.classList.add('loaded');
                setTimeout(() => {
                    pageLoader.remove();
                }, 500);
            }
        });
    </script>
</body>
</html>
