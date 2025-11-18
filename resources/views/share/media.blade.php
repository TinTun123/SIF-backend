<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="TESTING TITLE">
    <meta property="og:description" content="TESTING DESCRIPTION">
    <!-- <meta property="og:image" content="{{ $interview->thumbnail }}"> -->
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="video">
    <meta property="og:video" content="https://youtu.be/T-61Tuv9znU?si=lQr0J7vW2Nim3DNh">
    <meta property="og:video:url" content="https://youtu.be/T-61Tuv9znU?si=lQr0J7vW2Nim3DNh">
    <meta property="og:video:secure_url" content="https://youtu.be/T-61Tuv9znU?si=lQr0J7vW2Nim3DNh">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="1280">
    <meta property="og:video:height" content="720">
    <meta property="og:image" content="https://api.sif-mm.org/storage/thumbnails/db012cc1-6d56-47f7-810d-42a62b09bd64.jpg">

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