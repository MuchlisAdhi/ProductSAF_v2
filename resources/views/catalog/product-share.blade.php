<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PT. Sidoagung Farm">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $metaUrl }}">
    <meta property="og:image" content="{{ str_starts_with($metaImage, 'http://') || str_starts_with($metaImage, 'https://') ? $metaImage : rtrim((string) config('app.share_url', config('app.url', '')), '/').'/'.ltrim($metaImage, '/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalProductUrl }}">
    <meta http-equiv="refresh" content="0;url={{ $canonicalProductUrl }}">
</head>
<body>
    <p>Mengarahkan ke halaman produk: <a href="{{ $canonicalProductUrl }}">{{ $canonicalProductUrl }}</a></p>
</body>
</html>
