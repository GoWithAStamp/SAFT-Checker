<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SAFT Checker') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[var(--color-bg)] dark:bg-[#161513] min-h-screen flex flex-col antialiased">
    {{-- Navbar --}}
    <nav class="nav-glass sticky top-0 z-40 border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
        <div class="max-w-[1260px] mx-auto px-6 h-[62px] flex items-center justify-between">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 p-1.5 -m-1.5 rounded-[10px] hover:bg-[var(--color-surface-3)] dark:hover:bg-[#2e2b27] transition-colors">
                <div class="w-8 h-8 rounded-[9px] bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-[0_2px_6px_rgba(37,99,235,0.4)]">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-[17px] tracking-tight leading-none">
                    <b class="font-extrabold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">SAFT</b>
                    <span class="font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] ml-0.5">Checker</span>
                </span>
            </a>

            {{-- Right side --}}
            <div class="flex items-center gap-3.5">
                {{-- Language Switcher --}}
                <div class="flex bg-[var(--color-surface-3)] dark:bg-[#2e2b27] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[9px] p-0.5">
                    <a href="{{ route('locale.switch', 'pt') }}" wire:navigate
                       class="px-2.5 py-[5px] text-xs font-semibold rounded-[7px] tracking-wider transition-all {{ app()->getLocale() === 'pt' ? 'bg-[var(--color-surface)] dark:bg-[#201e1b] text-[var(--color-text-strong)] dark:text-[#f4f1ec] shadow-sm' : 'text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                        PT
                    </a>
                    <a href="{{ route('locale.switch', 'en') }}" wire:navigate
                       class="px-2.5 py-[5px] text-xs font-semibold rounded-[7px] tracking-wider transition-all {{ app()->getLocale() === 'en' ? 'bg-[var(--color-surface)] dark:bg-[#201e1b] text-[var(--color-text-strong)] dark:text-[#f4f1ec] shadow-sm' : 'text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                        EN
                    </a>
                </div>

                {{-- Divider --}}
                <div class="w-px h-[22px] bg-[var(--color-border-warm-2)] dark:bg-[#403b34]"></div>

                {{-- Dark Mode Toggle --}}
                <button @click="darkMode = !darkMode" class="w-[38px] h-[38px] rounded-[10px] border border-[var(--color-border-warm)] dark:border-[#332f2a] bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-muted)] dark:text-[#b3ada3] flex items-center justify-center hover:text-[var(--color-text-strong)] dark:hover:text-[#f4f1ec] hover:border-[var(--color-border-warm-2)] dark:hover:border-[#403b34] hover:bg-[var(--color-surface)] dark:hover:bg-[#201e1b] transition-all" :title="darkMode ? '{{ __('saft.theme_light') }}' : '{{ __('saft.theme_dark') }}'">
                    <svg x-show="darkMode" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!darkMode" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- Cold Start Notice --}}
    <div
        x-data="{ show: !localStorage.getItem('cold_start_dismissed') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-2 opacity-0"
        class="bg-amber-50 dark:bg-amber-950/30 border-b border-amber-200 dark:border-amber-800/40"
        x-cloak
    >
        <div class="max-w-[1260px] mx-auto px-6 py-2.5 flex items-center justify-between gap-4">
            <p class="text-[12.5px] text-amber-800 dark:text-amber-300/90 leading-snug">
                <span class="font-semibold">💤 {{ __('saft.cold_start_title') }}</span>
                {{ __('saft.cold_start_text') }}
            </p>
            <button
                @click="localStorage.setItem('cold_start_dismissed', 'true'); show = false"
                class="shrink-0 text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-200 transition-colors"
                aria-label="Dismiss"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <main class="max-w-[1260px] mx-auto px-6 py-8 flex-1 w-full">
        {{ $slot }}
    </main>

    <footer class="border-t border-[var(--color-border-warm)] dark:border-[#332f2a] bg-[var(--color-surface)] dark:bg-[#201e1b] mt-auto">
        <div class="max-w-[1260px] mx-auto px-6 py-4 flex items-center justify-between text-[12.5px] text-[var(--color-text-dim)] dark:text-[#827c72]">
            <span>{{ __('saft.footer_created_by') }} · <a href="https://cellocode.pt" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">cellocode.pt</a></span>
            <a href="{{ route('privacy') }}" class="hover:underline hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3] transition-colors">{{ __('saft.privacy_link') }}</a>
        </div>
    </footer>

    {{-- Cookie Consent Banner --}}
    <div
        x-data="{ show: !localStorage.getItem('cookie_consent') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 inset-x-0 z-50"
        x-cloak
    >
        <div class="bg-[var(--color-surface)] dark:bg-[#201e1b] border-t border-[var(--color-border-warm)] dark:border-[#332f2a] shadow-lg">
            <div class="max-w-[1260px] mx-auto px-6 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-[var(--color-text-muted)] dark:text-[#b3ada3]">
                        {{ __('saft.cookie_banner_text') }}
                        <a href="{{ route('privacy') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('saft.privacy_link') }}</a>.
                    </p>
                    <div class="flex items-center gap-3 shrink-0">
                        <button
                            @click="localStorage.setItem('cookie_consent', 'accepted'); show = false"
                            class="px-[18px] py-[11px] bg-blue-600 text-white text-sm font-semibold rounded-[10px] hover:bg-blue-700 transition-colors shadow-[0_1px_2px_rgba(37,99,235,0.4)]"
                        >
                            {{ __('saft.cookie_accept') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
