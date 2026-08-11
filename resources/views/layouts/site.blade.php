<!doctype html>
<html lang="ru" data-locale="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title data-i18n="meta.title">Green Energy Hub</title>
    @stack('head')
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</head>
<body class="site">
    @include('partials.site-header')
    <main id="content">@yield('content')</main>
    @include('partials.site-footer')
</body>
</html>
