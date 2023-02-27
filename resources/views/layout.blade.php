<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A:RP - @yield('title')</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900">

@yield('content')

@vite('resources/js/flowbite.min.js')
</body>
</html>
