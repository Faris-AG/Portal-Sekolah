<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Portal Akademik & Pembelajaran Siswa SMKN 1 Ciomas. Akses materi belajar, pantau catatan presensi, dan diskusi kelas terpadu.">

        <title inertia>{{ config('app.name', 'SKANIC Portal Akademik') }}</title>
        <link rel="icon" type="image/png" href="/images/logo-skanic.png">

        <!-- Fonts: preconnect untuk mencegah render-blocking -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
