<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $title ?? 'Scholarship Ranking' }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-slate-50 text-slate-900 antialiased">

    <div
        class="min-h-screen"
        x-data="{ mobileNavOpen: false }"
        @keydown.escape.window="mobileNavOpen = false"
    >

        {{ $slot }}

        {{-- Mobile navigation is shared by every page. --}}
        <button
            type="button"
            class="fixed bottom-5 right-5 z-40 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 lg:hidden"
            @click="mobileNavOpen = true"
            :aria-expanded="mobileNavOpen.toString()"
            aria-controls="mobile-navigation"
            aria-label="Open navigation"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div
            x-cloak
            x-show="mobileNavOpen"
            class="fixed inset-0 z-50 lg:hidden"
            role="dialog"
            aria-modal="true"
            aria-label="Main navigation"
        >
            <div class="absolute inset-0 bg-slate-950/40" @click="mobileNavOpen = false"></div>

            <nav
                id="mobile-navigation"
                x-show="mobileNavOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative flex h-full w-72 flex-col bg-white shadow-2xl"
            >
                <div class="flex h-20 items-center justify-between border-b border-slate-200 px-5">
                    <div>
                        <p class="text-sm font-bold text-slate-900">SHIBUST Scholarship</p>
                        <p class="text-xs text-slate-500">Ranking System</p>
                    </div>

                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click="mobileNavOpen = false" aria-label="Close navigation">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-1 p-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center rounded-lg px-3 py-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Dashboard</a>
                    <a href="{{ route('results') }}" class="flex items-center rounded-lg px-3 py-3 text-sm font-semibold {{ request()->routeIs('results') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Results</a>
                    <a href="{{ route('applicants.add') }}" class="flex items-center rounded-lg px-3 py-3 text-sm font-semibold {{ request()->routeIs('applicants.add') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Add Applicant</a>
                    <a href="{{ route('referrals') }}" class="flex items-center rounded-lg px-3 py-3 text-sm font-semibold {{ request()->routeIs('referrals') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Referrals</a>
                </div>
            </nav>
        </div>

    </div>

    @livewireScripts

</body>
</html>
