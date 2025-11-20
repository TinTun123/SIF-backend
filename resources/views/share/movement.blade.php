<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $movement->title_bur }}">
    <meta property="og:description" content="MOVEMENTS">
    <meta property="og:image" content="{{ $movement->cover_url }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="article:published_time" content="{{ $movement->story_date }}">
    <meta property="article:author" content="">
    <meta property="article:section" content="SIF">
    <meta property="og:type" content="article">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $movement->title_bur }}</title>

</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>