<!DOCTYPE html>
<html>
<head>
    <title>Demo XSS</title>
</head>
<body>

    <h1>Demo Perbedaan Blade</h1>

    <h2>Dengan Escaping</h2>
    <p>{{ $data }}</p>

    <h2>Tanpa Escaping</h2>
    <p>{!! $data !!}</p>

</body>
</html>