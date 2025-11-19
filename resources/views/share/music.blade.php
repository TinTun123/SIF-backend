<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $music->title }}">
    <meta property="og:description" content="ART MOVEMENT">
    <!-- <meta property="og:image" content="{{ $interview->thumbnail }}"> -->
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="video">
    <meta property="og:image" content="{{ $music->thumbnail }}">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $music->quote }}</title>
</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>