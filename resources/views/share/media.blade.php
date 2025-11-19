<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $interview->quote ?? 'PANEL DISCUSSION'}}">
    <meta property="og:description" content="The Spark">
    <!-- <meta property="og:image" content="{{ $interview->thumbnail }}"> -->
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="video">
    <meta property="og:image" content="{{ $interview->thumbnail }}">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $interview->quote }}</title>
</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>