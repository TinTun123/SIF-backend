<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $policy->title_bur }}">
    <meta property="og:description" content="{{ str_replace('#', ' ', Str::limit(strip_tags($policy->organizations), 140)) }}">
    <meta property="og:image" content="{{  }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="article:published_time" content="{{ $policy->date }}">
    <meta property="og:type" content="article">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $policy->title_bur }}</title>
</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>