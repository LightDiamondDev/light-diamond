<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}"/>

        <title>{{ config('app.name') }}</title>

        @vite('resources/css/app.css')
        <link id="theme-link" rel="stylesheet" href="{{ asset('themes/ld-cyan-light/theme.css') }}">
        @vite('resources/js/app.ts')
    </head>

    <body>
        <div id="app"></div>
    </body>
</html>
