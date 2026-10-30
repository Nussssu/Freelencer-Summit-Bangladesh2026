<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050E0A">
    @php($identity = \App\Support\Settings::identity())
    <link rel="icon" href="{{ $identity['favicon'] ?? '/favicon.svg' }}" @if(! $identity['favicon']) type="image/svg+xml" @endif>
    @isset($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endisset
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
    @include('partials.recaptcha')
    @if ($gtm = config('summit.analytics.gtm'))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($gtm));</script>
    @elseif ($ga4 = config('summit.analytics.ga4'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($ga4));</script>
    @endif
</head>
<body>
    @inertia
</body>
</html>
