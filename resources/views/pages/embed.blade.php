<!DOCTYPE html>
<html>
<head>
    <title></title>
    <script src="http://ascii.sknt.ru{{ elixir('js/app.js') }}"></script>
</head>
<body>
    @include('partials.embeddedAscii', ['ascii' => $ascii, 'embed' => true])
</body>
</html>