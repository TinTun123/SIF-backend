<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $article->title_bur }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($article->description), 140) }}">
    <meta property="og:image" content="{{ $article->cover_url }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $article->title_bur }}</title>
</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>