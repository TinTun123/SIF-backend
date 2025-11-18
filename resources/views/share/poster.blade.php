@php
$images = json_decode($poster->images, true);
$firstImage = $images[0] ?? null;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Required OG tags -->
    <meta property="og:title" content="Posters">
    <meta property="og:description" content="THE SPARK">
    <meta property="og:image" content="{{ $firstImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="book">
    <meta property="book:author" content="မီးလျှံ">
    <meta property="book:release_date" content="{{ $poster->date }}">

    <!-- Facebook app ID -->
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">

    <!-- <title>{{ $poster->title }}</title> -->
</head>

<body>
    <p>Facebook preview loading...</p>
</body>

</html>