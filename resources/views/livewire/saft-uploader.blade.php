<div>
    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- UPLOAD SCREEN --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if(!$result)
    <div class="max-w-[760px] mx-auto" style="padding: 64px 24px 48px;">
        {{-- Hero --}}
        <div class="text-center mb-[30px]">
            <span class="eyebrow inline-block px-3 py-[5px] rounded-full mb-[18px] font-mono">SAF-T (PT) &middot; V1.04_01</span>
            <h1 class="text-[38px] font-extrabold tracking-[-0.03em] leading-[1.08] text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-3.5">
                {{ __('saft.upload_title') }}
            </h1>
            <p class="text-[16.5px] leading-[1.55] text-[var(--color-text-muted)] dark:text-[#b3ada3] max-w-[560px] mx-auto">
                {{ __('saft.upload_description') }}
            </p>
        </div>

        {{-- Type Chooser --}}
        <div class="mb-[22px]">
            <p class="text-center text-[13px] font-semibold text-[var(--color-text-muted)] dark:text-[#b3ada3] mb-[13px]">
                {{ __('saft.upload_type_question') }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-[14px]">
                {{-- Faturacao --}}
                <button wire:click="setSaftType('billing')" type="button"
                    class="type-card relative text-left flex flex-col gap-1 p-5 pb-[18px] rounded-[14px] border-[1.5px] {{ $saftType === 'billing' ? 'selected' : '' }}">
                    <div class="absolute top-4 right-4">
                        <div class="w-5 h-5 rounded-full border-[1.5px] flex items-center justify-center transition-all {{ $saftType === 'billing' ? 'bg-blue-600 border-blue-600' : 'border-[var(--color-border-warm-strong)] dark:border-[#4a443c]' }}">
                            @if($saftType === 'billing')
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="w-[46px] h-[46px] rounded-xl flex items-center justify-center mb-2.5 transition-colors {{ $saftType === 'billing' ? 'bg-[color-mix(in_srgb,var(--color-accent)_14%,var(--color-surface))]' : 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27]' }} text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-[17px] font-bold tracking-[-0.02em] text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ __('saft.type_billing') }}</span>
                    <span class="text-[11.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] font-mono">SAF-T (PT) Comercial</span>
                    <span class="text-[12.5px] leading-[1.45] text-[var(--color-text-muted)] dark:text-[#b3ada3] mt-[5px]">{{ __('saft.type_billing_desc') }}</span>
                </button>

                {{-- Contabilidade --}}
                <button wire:click="setSaftType('accounting')" type="button"
                    class="type-card relative text-left flex flex-col gap-1 p-5 pb-[18px] rounded-[14px] border-[1.5px] {{ $saftType === 'accounting' ? 'selected' : '' }}">
                    <div class="absolute top-4 right-4">
                        <div class="w-5 h-5 rounded-full border-[1.5px] flex items-center justify-center transition-all {{ $saftType === 'accounting' ? 'bg-blue-600 border-blue-600' : 'border-[var(--color-border-warm-strong)] dark:border-[#4a443c]' }}">
                            @if($saftType === 'accounting')
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="w-[46px] h-[46px] rounded-xl flex items-center justify-center mb-2.5 transition-colors {{ $saftType === 'accounting' ? 'bg-[color-mix(in_srgb,var(--color-accent)_14%,var(--color-surface))]' : 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27]' }} text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-[17px] font-bold tracking-[-0.02em] text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ __('saft.type_accounting') }}</span>
                    <span class="text-[11.5px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] font-mono">SAF-T (PT) Contabilistico</span>
                    <span class="text-[12.5px] leading-[1.45] text-[var(--color-text-muted)] dark:text-[#b3ada3] mt-[5px]">{{ __('saft.type_accounting_desc') }}</span>
                </button>
            </div>
        </div>

        {{-- Dropzone --}}
        <div
            x-data="{ isDragging: false }"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
            class="dropzone-area rounded-[17px] p-[46px_32px] text-center"
            :class="isDragging && 'dragging'"
        >
            <input type="file" wire:model="saftFile" accept=".xml" class="hidden" x-ref="fileInput" id="saft-file-input">

            @if($fileName)
                {{-- File ready state --}}
                <div class="flex flex-col items-center gap-3.5">
                    <div class="w-16 h-16 rounded-[18px] flex items-center justify-center text-green-500" style="background: color-mix(in srgb, var(--color-ok) 11%, var(--color-surface));">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[18px] font-bold tracking-[-0.01em] text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $fileName }}</p>
                        <p class="text-[13px] text-[var(--color-text-dim)] dark:text-[#827c72] mt-1">{{ __('saft.upload_ready') }}</p>
                    </div>
                    <label for="saft-file-input" class="text-[13.5px] font-semibold text-blue-600 dark:text-blue-400 cursor-pointer hover:underline">
                        {{ __('saft.upload_select_another') }}
                    </label>
                </div>
            @else
                {{-- Empty state --}}
                <div class="flex flex-col items-center gap-3.5">
                    <div class="w-16 h-16 rounded-[18px] flex items-center justify-center text-blue-600 dark:text-blue-400 mb-0.5" style="background: color-mix(in srgb, var(--color-accent) 11%, var(--color-surface));">
                        <svg class="w-[30px] h-[30px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="text-[18px] font-bold tracking-[-0.01em] text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ __('saft.upload_drag') }}</p>
                    {{-- "ou" divider --}}
                    <div class="flex items-center gap-3 text-[var(--color-text-dim)] dark:text-[#827c72] text-[13px]">
                        <span class="w-[54px] h-px bg-[var(--color-border-warm-2)] dark:bg-[#403b34]"></span>
                        <span>{{ __('saft.upload_or') }}</span>
                        <span class="w-[54px] h-px bg-[var(--color-border-warm-2)] dark:bg-[#403b34]"></span>
                    </div>
                    {{-- Browse button --}}
                    <label for="saft-file-input" class="inline-flex items-center gap-2 px-[18px] py-[11px] bg-blue-600 text-white text-sm font-semibold rounded-[10px] hover:bg-blue-700 cursor-pointer transition-colors shadow-[0_1px_2px_rgba(37,99,235,0.4)] leading-none">
                        <svg class="w-[17px] h-[17px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('saft.upload_select') }}
                    </label>
                    {{-- Sample file link --}}
                    <button type="button" wire:click="loadSample" class="inline-flex items-center gap-[6px] text-[13.5px] font-semibold text-blue-600 dark:text-blue-400 hover:gap-[9px] transition-all p-[6px]">
                        {{ __('saft.upload_use_sample') }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                    {{-- Hint --}}
                    <span class="text-[11.5px] text-[var(--color-text-dim)] dark:text-[#827c72] mt-1 font-mono">{{ __('saft.upload_hint') }}</span>
                </div>
            @endif
        </div>

        @if($errorMessage)
            <div class="mt-4 p-4 rounded-[11px] flex items-center gap-3 bg-red-50 dark:bg-red-900/15 border border-red-200 dark:border-red-800/40">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
        @endif

        @if($fileName)
            <div class="mt-6 flex justify-center">
                <button wire:click="validate_saft" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-[10px] hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-[0_1px_2px_rgba(37,99,235,0.4)]">
                    <span wire:loading.remove wire:target="validate_saft">
                        <svg class="w-5 h-5 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('saft.upload_validate') }}
                    </span>
                    <span wire:loading wire:target="validate_saft" class="flex items-center">
                        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('saft.upload_validating') }}
                    </span>
                </button>
            </div>
        @endif

        {{-- Secure line --}}
        <div class="flex items-center justify-center gap-[7px] text-[12.5px] text-[var(--color-text-dim)] dark:text-[#827c72]" style="margin: 18px 0 40px;">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>{{ __('saft.gdpr_secure_line') }}</span>
        </div>

        {{-- Feature Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-[14px]">
            {{-- Validacao fiscal --}}
            <div class="feature-card border rounded-[11px] p-[20px_18px]">
                <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-blue-600 dark:text-blue-400 mb-3">
                    <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="text-[14.5px] font-bold tracking-[-0.01em] text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-[5px]">{{ __('saft.feat1_title') }}</div>
                <p class="text-[13px] leading-[1.5] text-[var(--color-text-muted)] dark:text-[#b3ada3] m-0">{{ __('saft.feat1_body') }}</p>
            </div>
            {{-- Dados organizados --}}
            <div class="feature-card border rounded-[11px] p-[20px_18px]">
                <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-blue-600 dark:text-blue-400 mb-3">
                    <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-[14.5px] font-bold tracking-[-0.01em] text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-[5px]">{{ __('saft.feat2_title') }}</div>
                <p class="text-[13px] leading-[1.5] text-[var(--color-text-muted)] dark:text-[#b3ada3] m-0">{{ __('saft.feat2_body') }}</p>
            </div>
            {{-- Exportacao --}}
            <div class="feature-card border rounded-[11px] p-[20px_18px]">
                <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-blue-600 dark:text-blue-400 mb-3">
                    <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <div class="text-[14.5px] font-bold tracking-[-0.01em] text-[var(--color-text-strong)] dark:text-[#f4f1ec] mb-[5px]">{{ __('saft.feat3_title') }}</div>
                <p class="text-[13px] leading-[1.5] text-[var(--color-text-muted)] dark:text-[#b3ada3] m-0">{{ __('saft.feat3_body') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- RESULTS SCREEN --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    @if($result)
    <div>
        {{-- Results Header Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-[42px] h-[42px] rounded-[11px] shrink-0 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-sm font-bold font-mono tracking-tight text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $fileName }}</span>
                        @if($isAccountingSaft)
                            <span class="type-tag-accounting inline-flex items-center gap-1 text-[11px] font-bold px-2 py-[3px] rounded-[7px] leading-tight">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18"/></svg>
                                {{ __('saft.type_accounting') }}
                            </span>
                        @else
                            <span class="type-tag-billing inline-flex items-center gap-1 text-[11px] font-bold px-2 py-[3px] rounded-[7px] leading-tight">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6"/></svg>
                                {{ __('saft.type_billing') }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 text-[12.5px] text-[var(--color-text-dim)] dark:text-[#827c72] mt-0.5">
                        @if(!empty($saftData['header']['CompanyName']))
                            <span>{{ $saftData['header']['CompanyName'] }}</span>
                            <span>&middot;</span>
                        @endif
                        @if(!empty($saftData['header']['TaxRegistrationNumber']))
                            <span class="font-mono">{{ $saftData['header']['TaxRegistrationNumber'] }}</span>
                            <span>&middot;</span>
                        @endif
                        @if(!empty($saftData['header']['FiscalYear']))
                            <span>{{ $saftData['header']['FiscalYear'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <button wire:click="exportAllCsv" class="inline-flex items-center gap-2 px-[18px] py-[11px] bg-blue-600 text-white text-sm font-semibold rounded-[10px] hover:bg-blue-700 transition-colors shadow-[0_1px_2px_rgba(37,99,235,0.4)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    {{ __('saft.export_all') }}
                </button>
                <button wire:click="resetUpload" class="inline-flex items-center gap-2 px-[18px] py-[11px] bg-[var(--color-surface)] dark:bg-[#201e1b] text-[var(--color-text-muted)] dark:text-[#b3ada3] text-sm font-semibold rounded-[10px] border border-[var(--color-border-warm)] dark:border-[#332f2a] hover:text-[var(--color-text-strong)] dark:hover:text-[#f4f1ec] hover:border-[var(--color-border-warm-2)] dark:hover:border-[#403b34] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('saft.results_new') }}
                </button>
            </div>
        </div>

        {{-- Main Tabs --}}
        <div class="flex items-center gap-0 mb-5 border-b border-[var(--color-border-warm)] dark:border-[#332f2a]">
            <button wire:click="setActiveTab('validation')" class="relative px-5 py-3 text-sm font-semibold transition-colors flex items-center gap-2 {{ $activeTab === 'validation' ? 'text-[var(--color-text-strong)] dark:text-[#f4f1ec]' : 'text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                {{ __('saft.tab_validation') }}
                @if($summary['errors'] > 0)
                    <span class="pill-count bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">{{ $summary['errors'] }}</span>
                @endif
                @if($activeTab === 'validation')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"></span>
                @endif
            </button>
            <button wire:click="setActiveTab('data')" class="relative px-5 py-3 text-sm font-semibold transition-colors flex items-center gap-2 {{ $activeTab === 'data' ? 'text-[var(--color-text-strong)] dark:text-[#f4f1ec]' : 'text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                {{ __('saft.tab_data') }}
                @if($activeTab === 'data')
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"></span>
                @endif
            </button>
            @if($isAccountingSaft)
                <button wire:click="setActiveTab('plano')" class="relative px-5 py-3 text-sm font-semibold transition-colors flex items-center gap-2 {{ $activeTab === 'plano' ? 'text-[var(--color-text-strong)] dark:text-[#f4f1ec]' : 'text-[var(--color-text-dim)] dark:text-[#827c72] hover:text-[var(--color-text-muted)] dark:hover:text-[#b3ada3]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('saft.tab_plano') }}
                    @if($planoComparison && ($planoComparison['summary']['missing_in_saft'] > 0 || $planoComparison['summary']['extra_in_saft'] > 0 || $planoComparison['summary']['description_diff'] > 0))
                        <span class="pill-count bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">{{ $planoComparison['summary']['missing_in_saft'] + $planoComparison['summary']['extra_in_saft'] + $planoComparison['summary']['description_diff'] + $planoComparison['summary']['grouping_diff'] }}</span>
                    @endif
                    @if($activeTab === 'plano')
                        <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"></span>
                    @endif
                </button>
            @endif
        </div>

        @if($activeTab === 'validation')
            @include('livewire.partials.validation-results')
        @endif

        @if($activeTab === 'data' && $saftData)
            @include('livewire.partials.data-tables')
        @endif

        @if($activeTab === 'plano' && $isAccountingSaft)
            @include('livewire.partials.plano-comparison')
        @endif
    </div>
    @endif
</div>
