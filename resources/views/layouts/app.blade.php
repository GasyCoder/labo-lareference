<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.png') }}">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/dist/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/yaireo/tagify/dist/tagify.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/dist/simplebar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bs-stepper/dist/css/bs-stepper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}">

    <script src="https://kit.fontawesome.com/b09d10e9b2.js" crossorigin="anonymous"></script>
    <!-- Scripts -->
    <script src="{{ asset('assets/js/vendors/darkMode.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Dans votre layout -->
    @stack('styles')
</head>
<body>
    <!-- Wrapper -->
<div id="db-wrapper">
<!-- navbar vertical -->
<x-navbar-vertical/>

<!-- Page Content -->
<main id="page-content">

<div class="header">
<!-- navbar -->
<x-navbar/>
</div>
<!-- Page Header -->

{{ $slot }}

</main>
</div>

<!-- Script -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Libs JS -->
<script src="{{ asset('assets/libs/bs-stepper/dist/js/bs-stepper.min.js') }}"></script>
<script src="{{ asset('assets/js/vendors/beStepper.js') }}"></script>
<script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/yaireo/tagify/dist/tagify.min.js') }}"></script>
<!-- Theme JS -->
<script src="{{ asset('assets/js/theme.min.js') }}"></script>
<!-- Additional libraries -->
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/vendors/chart.js') }}"></script>
<script src="{{ asset('assets/libs/flatpickr/dist/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/js/vendors/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/vendors/validation.js')}}"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>
<script src="{{ asset('assets/js/vendors/choice.js')}}"></script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<x-livewire-alert::scripts />

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const anchor = window.location.hash.substring(1); // Récupère l'ancre (ex : 'actives')
        if (anchor) {
            Livewire.emit('switchTab', anchor);
        }
    });

    window.addEventListener('hashchange', function () {
        const anchor = window.location.hash.substring(1); // Met à jour l'état si l'ancre change
        Livewire.emit('switchTab', anchor);
    });
</script>

@stack('scripts')
</body>
</html>
