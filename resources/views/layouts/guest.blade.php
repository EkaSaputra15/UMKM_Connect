<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UMKM Connect') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8">
        <div class="mb-6 text-center">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-sm shadow-indigo-100/50">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
                <div class="text-left">
                    <div class="text-xl font-extrabold text-slate-900 tracking-tight">UMKM Connect</div>
                    <div class="text-xs font-medium text-indigo-600">CRM &amp; Loyalty Platform</div>
                </div>
            </a>
        </div>

        <div class="w-full sm:max-w-md bg-white rounded-2xl border border-slate-200/80 shadow-sm shadow-indigo-100/50 p-6 sm:p-7">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
