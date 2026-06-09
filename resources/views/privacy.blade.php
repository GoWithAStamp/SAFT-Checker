<x-layouts.app>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-[38px] font-extrabold tracking-tight text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-8">{{ __('saft.privacy_title') }}</h1>

        <div class="space-y-8">

            <p class="text-sm text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.privacy_last_updated') }}: 2026-06-09</p>

            @php
                $sections = [
                    ['title' => 'privacy_what_title', 'text' => 'privacy_what_text'],
                    ['title' => 'privacy_data_title', 'text' => 'privacy_data_text', 'list' => ['privacy_data_nif', 'privacy_data_names', 'privacy_data_addresses', 'privacy_data_financial']],
                    ['title' => 'privacy_storage_title', 'text' => 'privacy_storage_text', 'list' => ['privacy_storage_no_db', 'privacy_storage_no_persist', 'privacy_storage_temp', 'privacy_storage_no_third']],
                    ['title' => 'privacy_legal_title', 'text' => 'privacy_legal_text'],
                ];
            @endphp

            @foreach($sections as $section)
                <section class="bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[11px] p-6">
                    <h2 class="text-lg font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-3">{{ __('saft.' . $section['title']) }}</h2>
                    <p class="text-[var(--color-text-muted)] dark:text-[#b3ada3] leading-relaxed">{{ __('saft.' . $section['text']) }}</p>
                    @if(!empty($section['list']))
                        <ul class="list-disc list-inside text-[var(--color-text-muted)] dark:text-[#b3ada3] mt-3 space-y-1">
                            @foreach($section['list'] as $item)
                                <li>{{ __('saft.' . $item) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            {{-- Cookies --}}
            <section class="bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[11px] p-6">
                <h2 class="text-lg font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-3">{{ __('saft.privacy_cookies_title') }}</h2>
                <p class="text-[var(--color-text-muted)] dark:text-[#b3ada3] leading-relaxed">{{ __('saft.privacy_cookies_text') }}</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-[13.5px]">
                        <thead>
                            <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
                                <th class="px-4 py-2 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">Cookie</th>
                                <th class="px-4 py-2 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.privacy_cookie_purpose') }}</th>
                                <th class="px-4 py-2 text-left text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ __('saft.privacy_cookie_duration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['name' => 'saft_checker_session', 'purpose' => 'privacy_cookie_session', 'duration' => 'privacy_cookie_session_duration'],
                                ['name' => 'XSRF-TOKEN', 'purpose' => 'privacy_cookie_csrf', 'duration' => 'privacy_cookie_session_duration'],
                                ['name' => 'darkMode', 'purpose' => 'privacy_cookie_dark', 'duration' => 'privacy_cookie_persistent'],
                                ['name' => 'cookie_consent', 'purpose' => 'privacy_cookie_consent', 'duration' => 'privacy_cookie_1year'],
                            ] as $i => $cookie)
                                <tr class="border-b border-[var(--color-border-warm)] dark:border-[#332f2a] {{ $i % 2 === 1 ? 'bg-[var(--color-surface-2)] dark:bg-[#262320]' : '' }}">
                                    <td class="px-4 py-2 font-mono font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $cookie['name'] }}</td>
                                    <td class="px-4 py-2 text-[var(--color-text-muted)] dark:text-[#b3ada3]">{{ __('saft.' . $cookie['purpose']) }}</td>
                                    <td class="px-4 py-2 text-[var(--color-text-dim)] dark:text-[#827c72]">{{ __('saft.' . $cookie['duration']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Rights --}}
            <section class="bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[11px] p-6">
                <h2 class="text-lg font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-3">{{ __('saft.privacy_rights_title') }}</h2>
                <p class="text-[var(--color-text-muted)] dark:text-[#b3ada3] leading-relaxed">{{ __('saft.privacy_rights_text') }}</p>
            </section>

            {{-- Contact --}}
            <section class="bg-[var(--color-surface)] dark:bg-[#201e1b] border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[11px] p-6">
                <h2 class="text-lg font-bold text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-3">{{ __('saft.privacy_contact_title') }}</h2>
                <p class="text-[var(--color-text-muted)] dark:text-[#b3ada3] leading-relaxed">{{ __('saft.privacy_contact_text') }}</p>
            </section>

        </div>

        <div class="mt-8">
            <a href="{{ route('home') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-semibold">&larr; {{ __('saft.privacy_back') }}</a>
        </div>
    </div>
</x-layouts.app>
