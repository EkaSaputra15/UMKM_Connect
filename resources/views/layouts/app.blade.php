<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'UMKM Connect') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    @php
        $user = auth()->user();
        $store = $panelStore ?? $user?->store;
        $isOwner = $user?->isOwner() ?? false;
    @endphp

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row">
        {{-- Mobile/Tablet Header --}}
        <header class="lg:hidden no-print bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-2.5">
                <button @click="sidebarOpen = !sidebarOpen" type="button" class="p-2 rounded-xl text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm shadow-indigo-100/50">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900 leading-tight">UMKM Connect</div>
                        <div class="text-[11px] text-slate-500 leading-tight">{{ $store?->name }}</div>
                    </div>
                </div>
            </div>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-sm hover:bg-indigo-700 active:scale-[0.99] transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Kasir
            </a>
        </header>

        {{-- Backdrop for mobile sidebar --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden"></div>

        {{-- Column 1: Left Sidebar (~250px) --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="no-print fixed lg:sticky top-0 left-0 z-40 h-screen w-[250px] shrink-0 bg-white border-r border-slate-200/80 flex flex-col justify-between transition-transform duration-200 ease-out">
            <div class="p-4 overflow-y-auto">
                {{-- Brand Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 py-2.5 mb-5">
                    @if($store?->logo_path)
                        <img src="{{ asset('storage/' . $store->logo_path) }}" alt="{{ $store->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm shadow-indigo-100/50">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="text-base font-bold text-slate-900 tracking-tight truncate">UMKM Connect</div>
                        <div class="text-xs font-medium text-indigo-600 truncate">{{ $store?->name ?? 'Workspace' }}</div>
                    </div>
                </a>

                {{-- Primary Navigation --}}
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-3 mb-2">Menu Utama</div>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('transactions.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('transactions.create') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        <i data-lucide="calculator" class="w-4 h-4 shrink-0"></i>
                        <span>Kasir &amp; Transaksi</span>
                    </a>

                    <a href="{{ route('customers.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                        <span>Pelanggan CRM</span>
                    </a>

                    <a href="{{ route('transactions.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        <i data-lucide="receipt" class="w-4 h-4 shrink-0"></i>
                        <span>Riwayat Transaksi</span>
                    </a>

                    @if($isOwner)
                        <div class="pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-3">Owner &amp; Growth</div>

                        <a href="{{ route('broadcasts.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('broadcasts.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                            <span>Broadcast Email</span>
                        </a>

                        <a href="{{ route('reports.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
                            <span>Laporan &amp; Analitik</span>
                        </a>

                        <a href="{{ route('services.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('services.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
                            <span>Katalog Layanan</span>
                        </a>

                        <a href="{{ route('staff.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('staff.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                            <span>Staf &amp; Kasir</span>
                        </a>

                        <a href="{{ route('settings.edit') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-100/50' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                            <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                            <span>Pengaturan Toko</span>
                        </a>
                    @endif
                </nav>
            </div>

            {{-- Logged-in User Profile --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(mb_substr($user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-slate-900 truncate">{{ $user?->name }}</div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $isOwner ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $isOwner ? 'Owner' : 'Kasir' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile.edit') }}" class="flex-1 text-center px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-rose-600 hover:bg-rose-50 transition">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Column 2: Main Content Area (Flex-grow) --}}
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-7">
            @isset($header)
                <div class="mb-5">
                    {{ $header }}
                </div>
            @endisset

            @if (session('success'))
                <div class="no-print mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3 shadow-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="no-print mb-5 rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 flex items-start gap-3 shadow-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{ $slot }}
        </main>

        {{-- Column 3: Right Panel (~300px) --}}
        <aside class="no-print w-full lg:w-[270px] xl:w-[300px] shrink-0 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto bg-white border-t lg:border-t-0 lg:border-l border-slate-200/80 p-4 sm:p-5 space-y-5">
            {{-- Real-Time Digital Clock Widget --}}
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white p-4 shadow-sm shadow-indigo-100/50"
                 x-data="{
                     timeStr: '',
                     dateStr: '',
                     tick() {
                         const now = new Date();
                         this.timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                         this.dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                     }
                 }"
                 x-init="tick(); setInterval(() => tick(), 1000)">
                <div class="flex items-center justify-between text-indigo-100 text-xs font-medium mb-1">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Waktu Operasional
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-semibold">LIVE</span>
                </div>
                <div class="text-2xl font-extrabold tracking-tight tabular-nums" x-text="timeStr">--:--:--</div>
                <div class="text-xs text-indigo-100 mt-1" x-text="dateStr">Memuat tanggal...</div>
            </div>

            {{-- Email/SMTP Service Status Widget --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-900">
                        <i data-lucide="mail-check" class="w-4 h-4 text-indigo-600"></i>
                        Status Email / SMTP
                    </div>
                    @if($panelEmailStatus['active'] ?? false)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Mode Log
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-600 space-y-1">
                    <div>Driver: <span class="font-semibold text-slate-800">{{ $panelEmailStatus['mailer'] ?? 'LOG' }}</span></div>
                    <div class="truncate">Pengirim: <span class="font-medium text-slate-800">{{ $panelEmailStatus['sender_email'] ?? '-' }}</span></div>
                </div>
                @if($isOwner)
                    <a href="{{ route('settings.edit') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        Kelola Pengaturan SMTP
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            {{-- Recent Transaction Activity Summary --}}
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Aktivitas Terkini</h3>
                    <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Semua</a>
                </div>

                @if(isset($latestPanelTransactions) && $latestPanelTransactions->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($latestPanelTransactions as $panelTx)
                            <a href="{{ route('transactions.show', $panelTx) }}" class="block p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50/70 border border-slate-100 transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-slate-900 truncate">{{ $panelTx->customer?->name ?? 'Pelanggan' }}</span>
                                    <span class="text-xs font-bold text-indigo-600 shrink-0">Rp {{ number_format((float)$panelTx->total_paid, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 mt-1 text-[11px] text-slate-500">
                                    <span class="truncate">{{ $panelTx->invoice_code }}</span>
                                    <span>{{ $panelTx->created_at?->diffForHumans(short: true) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-xs text-slate-500 py-4 text-center">
                        Belum ada aktivitas transaksi hari ini.
                    </div>
                @endif
            </div>
        </aside>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
