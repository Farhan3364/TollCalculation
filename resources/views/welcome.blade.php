<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Toll Calculation</title>
        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        {{-- App CSS --}}
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    </head>
    <body>
        <div id="app">
            {{-- Vue.js component For Toll Calculations --}}
            <toll-calculation-component></toll-calculation-component>
        </div>
        {{-- App js --}}
        <script src="{{ asset('js/app.js') }}"></script>
        {{-- jQuery --}}
        <script src="{{ asset('js/jquery.min.js') }}"></script>
    </body>
</html>
