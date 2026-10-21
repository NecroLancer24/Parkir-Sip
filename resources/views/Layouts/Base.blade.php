<!DOCTYPE html>
<html lang="en" class=''>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ config('app.name', 'Laravel') }} - @yield('Title') </title>
</head>

<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    @include('Layouts.Navbar')

    <main class="flex-1 container mx-auto px-4 py-6">
        @yield('Content')
    </main>

    @include('Layouts.Footer')
</body>

</html>
