<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="{{ $interview->quote }}">
    <meta property="og:description" content="{{ $interview->type }}">
    <meta property="og:image" content="{{ $interview->thumbnail }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="video.other">
    <meta property="og:video" content="https://api.sif-mm.org/storage/interviews/638dcb03-156e-44ae-90d3-cc64940433e1.mp4">
    <meta property="og:video:type" content="text/html">


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