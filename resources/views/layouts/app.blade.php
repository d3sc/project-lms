<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LMS') &middot; {{ config('app.name', 'LMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans">
<div class="app-shell" x-data="{ sidebarOpen: false }">

    {{-- Top bar (mobile) --}}
    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">L</span>
            <span class="text-sm font-semibold text-slate-900">{{ config('app.name', 'LMS') }}</span>
        </a>
        <button type="button" class="btn-ghost btn-sm" @click="sidebarOpen = !sidebarOpen" aria-label="Buka menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    <div class="flex flex-1">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar --}}
        <aside class="app-sidebar fixed inset-y-0 left-0 z-40 -translate-x-full transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-full flex-col">
                <div class="hidden items-center gap-2.5 border-b border-slate-100 px-5 py-5 lg:flex">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">L</span>
                    <div>
                        <p class="text-sm font-semibold leading-tight text-slate-900">{{ config('app.name', 'LMS') }}</p>
                        <p class="text-xs text-slate-500">Online Examination</p>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi utama"
                     @click="if ($event.target.closest('a')) sidebarOpen = false">
                    @auth
                        @if (auth()->user()->isAdmin())
                            <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ringkasan</p>
                            <a href="{{ route('admin.dashboard') }}"
                               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">
                                <span aria-hidden="true">⌂</span> Dashboard
                            </a>

                            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Operasional Ujian</p>
                            <a href="{{ route('admin.exams.index') }}"
                               class="nav-link {{ request()->routeIs('admin.exams.*') ? 'nav-link-active' : '' }}">
                                <span aria-hidden="true">▣</span> Ujian & Soal
                            </a>
                            <a href="{{ route('admin.monitoring.index') }}"
                               class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'nav-link-active' : '' }}">Monitoring</a>
                            <a href="{{ route('admin.grading.index') }}"
                               class="nav-link {{ request()->routeIs('admin.grading.*') ? 'nav-link-active' : '' }}">Penilaian Esai</a>
                            <a href="{{ route('admin.results.index') }}"
                               class="nav-link {{ request()->routeIs('admin.results.*') ? 'nav-link-active' : '' }}">Hasil Ujian</a>

                            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Administrasi</p>
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Pengguna</a>
                        @else
                            <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Belajar</p>
                            <a href="{{ route('student.dashboard') }}"
                               class="nav-link {{ request()->routeIs('student.dashboard', 'student.exams.show') ? 'nav-link-active' : '' }}">
                                <span aria-hidden="true">⌂</span> Ujian Tersedia
                            </a>
                            <a href="{{ route('student.exams.results.index') }}"
                               class="nav-link {{ request()->routeIs('student.exams.results.*') ? 'nav-link-active' : '' }}">
                                <span aria-hidden="true">▤</span> Riwayat Ujian
                            </a>
                        @endif
                    @endauth
                </nav>

                @auth
                    <div class="border-t border-slate-100 p-3">
                        <div class="mb-2 flex items-center gap-2.5 rounded-lg px-2 py-2">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs capitalize text-slate-500">{{ auth()->user()->role }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('profile.edit') }}" class="btn-secondary btn-sm flex-1">Profil</a>
                            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                @csrf
                                <button type="submit" class="btn-ghost btn-sm w-full">Keluar</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @hasSection('header')
                    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            @yield('header')
                        </div>
                        @hasSection('actions')
                            <div class="flex flex-wrap gap-2">@yield('actions')</div>
                        @endif
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert-success mb-5">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert-error mb-5">
                        <ul class="list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
                @stack('scripts')
            </main>
        </div>
    </div>
</div>
</body>
</html>