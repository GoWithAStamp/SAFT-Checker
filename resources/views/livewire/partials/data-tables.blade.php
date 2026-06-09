<div>
    {{-- Data Sub-nav (Pill bar) --}}
    @php
        $dataTabs = [
            'header' => ['label' => __('saft.data_header'), 'count' => null],
            'customers' => ['label' => __('saft.data_customers'), 'count' => count($saftData['customers'])],
            'products' => ['label' => __('saft.data_products'), 'count' => count($saftData['products'])],
            'tax_table' => ['label' => __('saft.data_tax_table'), 'count' => count($saftData['tax_table'])],
            'invoices' => ['label' => __('saft.data_invoices'), 'count' => count($saftData['invoices'])],
            'payments' => ['label' => __('saft.data_payments'), 'count' => count($saftData['payments'])],
            'movements' => ['label' => __('saft.data_movements'), 'count' => count($saftData['movements'])],
            'working_documents' => ['label' => __('saft.data_working_docs'), 'count' => count($saftData['working_documents'])],
        ];
        if (!empty($saftData['suppliers'])) {
            $dataTabs = array_slice($dataTabs, 0, 2, true)
                + ['suppliers' => ['label' => __('saft.data_suppliers'), 'count' => count($saftData['suppliers'])]]
                + array_slice($dataTabs, 2, null, true);
        }
        if ($isAccountingSaft) {
            $dataTabs['general_ledger_accounts'] = ['label' => __('saft.data_accounts'), 'count' => count($saftData['general_ledger_accounts'] ?? [])];
            $dataTabs['general_ledger_entries'] = ['label' => __('saft.data_ledger_entries'), 'count' => count($saftData['general_ledger_entries'] ?? [])];
        }

        // Section title + record count for current tab
        $currentTabLabel = $dataTabs[$activeDataTab]['label'] ?? '';
        $currentTabCount = $dataTabs[$activeDataTab]['count'] ?? null;
    @endphp

    <div class="pill-nav mb-5">
        @foreach($dataTabs as $key => $tab)
            @if($isAccountingSaft && $key === 'general_ledger_accounts')
                {{-- Divider before accounting tabs --}}
                <div class="w-px h-6 bg-[var(--color-border-warm-2)] dark:bg-[#403b34] shrink-0 self-center mx-1"></div>
            @endif
            <button
                wire:click="setActiveDataTab('{{ $key }}')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-semibold rounded-[9px] whitespace-nowrap transition-all shrink-0 {{ $activeDataTab === $key ? 'bg-blue-600 text-white shadow-sm' : 'bg-[var(--color-surface)] dark:bg-[#201e1b] text-[var(--color-text-muted)] dark:text-[#b3ada3] border border-[var(--color-border-warm)] dark:border-[#332f2a] hover:text-[var(--color-text-strong)] dark:hover:text-[#f4f1ec] hover:border-[var(--color-border-warm-2)] dark:hover:border-[#403b34]' }}"
            >
                {{ $tab['label'] }}
                @if($tab['count'] !== null)
                    <span class="pill-count {{ $activeDataTab === $key ? 'bg-white/20 text-white' : 'bg-[var(--color-surface-3)] dark:bg-[#2e2b27] text-[var(--color-text-dim)] dark:text-[#827c72]' }}">{{ $tab['count'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Toolbar: section title + search + export --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <h3 class="text-[17px] font-bold tracking-tight text-[var(--color-text-strong)] dark:text-[#f4f1ec]">{{ $currentTabLabel }}</h3>
            @if($currentTabCount !== null)
                <span class="text-[12.5px] text-[var(--color-text-dim)] dark:text-[#827c72]">{{ $currentTabCount }} {{ __('saft.results_records') }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if($activeDataTab !== 'header')
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--color-text-dim)] dark:text-[#827c72]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="{{ __('saft.search_placeholder') }}" class="pl-9 pr-3 py-2 text-[13px] rounded-[9px] border border-[var(--color-border-warm)] dark:border-[#332f2a] bg-[var(--color-surface)] dark:bg-[#201e1b] text-[var(--color-text-strong)] dark:text-[#f4f1ec] placeholder-[var(--color-text-dim)] dark:placeholder-[#827c72] focus:outline-none focus:border-blue-500 dark:focus:border-blue-400 w-48 transition-colors">
                </div>
            @endif
            <button wire:click="exportCsv('{{ $activeDataTab }}')" class="inline-flex items-center gap-1.5 px-3 py-2 text-[13px] font-semibold rounded-[9px] border border-green-200 dark:border-green-800/40 bg-green-50 dark:bg-green-900/15 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                CSV
            </button>
            <button wire:click="exportXml('{{ $activeDataTab }}')" class="inline-flex items-center gap-1.5 px-3 py-2 text-[13px] font-semibold rounded-[9px] border border-orange-200 dark:border-orange-800/40 bg-orange-50 dark:bg-orange-900/15 text-orange-700 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                XML
            </button>
        </div>
    </div>

    {{-- Table container --}}
    <div class="bg-[var(--color-surface)] dark:bg-[#201e1b] rounded-[11px] shadow-sm border border-[var(--color-border-warm)] dark:border-[#332f2a] overflow-hidden">

        {{-- ── Header ──────────────────────────────────── --}}
        @if($activeDataTab === 'header' && !empty($saftData['header']))
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @php
                        $headerLabels = [
                            'AuditFileVersion' => __('saft.header_version'),
                            'CompanyID' => __('saft.header_company_id'),
                            'TaxRegistrationNumber' => __('saft.header_nif'),
                            'TaxAccountingBasis' => __('saft.header_tax_basis'),
                            'CompanyName' => __('saft.header_company_name'),
                            'BusinessName' => __('saft.header_business_name'),
                            'FiscalYear' => __('saft.header_fiscal_year'),
                            'StartDate' => __('saft.header_start_date'),
                            'EndDate' => __('saft.header_end_date'),
                            'CurrencyCode' => __('saft.header_currency'),
                            'DateCreated' => __('saft.header_date_created'),
                            'TaxEntity' => __('saft.header_tax_entity'),
                            'ProductCompanyTaxID' => __('saft.header_product_nif'),
                            'SoftwareCertificateNumber' => __('saft.header_software_cert'),
                            'ProductID' => __('saft.header_software'),
                            'ProductVersion' => __('saft.header_software_version'),
                            'Telephone' => __('saft.header_telephone'),
                            'Email' => __('saft.header_email'),
                            'Website' => __('saft.header_website'),
                            'AddressDetail' => __('saft.header_address'),
                            'City' => __('saft.header_city'),
                            'PostalCode' => __('saft.header_postal_code'),
                            'Country' => __('saft.header_country'),
                            'TaxonomyReference' => __('saft.header_taxonomy_ref'),
                        ];
                        $wideFields = ['CompanyName', 'BusinessName', 'AddressDetail', 'ProductID'];
                    @endphp
                    @foreach($headerLabels as $key => $label)
                        @if(!empty($saftData['header'][$key]))
                            <div class="{{ in_array($key, $wideFields) ? 'sm:col-span-2' : '' }} border border-[var(--color-border-warm)] dark:border-[#332f2a] rounded-[9px] p-3 bg-[var(--color-surface-2)] dark:bg-[#262320]">
                                <p class="text-[11px] font-semibold text-[var(--color-text-dim)] dark:text-[#827c72] uppercase tracking-[0.04em]">{{ $label }}</p>
                                <p class="text-sm font-semibold text-[var(--color-text-strong)] dark:text-[#f4f1ec] mt-1 {{ in_array($key, ['TaxRegistrationNumber', 'CompanyID', 'AuditFileVersion', 'FiscalYear', 'CurrencyCode', 'SoftwareCertificateNumber', 'ProductCompanyTaxID', 'PostalCode']) ? 'font-mono' : '' }}">
                                    @if($key === 'TaxonomyReference')
                                        {{ $saftData['header'][$key] === 'S' ? 'S — SNC-ME / Micro-entidades' : ($saftData['header'][$key] === 'M' ? 'M — SNC Geral' : $saftData['header'][$key]) }}
                                    @elseif($key === 'TaxAccountingBasis')
                                        {{ $saftData['header'][$key] }} — {{ ['F' => __('saft.tax_basis_f'), 'C' => __('saft.tax_basis_c'), 'I' => __('saft.tax_basis_i'), 'S' => __('saft.tax_basis_s'), 'E' => __('saft.tax_basis_e'), 'P' => __('saft.tax_basis_p')][$saftData['header'][$key]] ?? '' }}
                                    @else
                                        {{ $saftData['header'][$key] }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Customers ───────────────────────────────── --}}
        @if($activeDataTab === 'customers')
            @include('livewire.partials.tables.customers')
        @endif

        {{-- ── Suppliers ───────────────────────────────── --}}
        @if($activeDataTab === 'suppliers')
            @include('livewire.partials.tables.suppliers')
        @endif

        {{-- ── Products ────────────────────────────────── --}}
        @if($activeDataTab === 'products')
            @include('livewire.partials.tables.products')
        @endif

        {{-- ── Tax Table ───────────────────────────────── --}}
        @if($activeDataTab === 'tax_table')
            @include('livewire.partials.tables.tax-table')
        @endif

        {{-- ── Invoices ────────────────────────────────── --}}
        @if($activeDataTab === 'invoices')
            @include('livewire.partials.tables.invoices')
        @endif

        {{-- ── Payments ────────────────────────────────── --}}
        @if($activeDataTab === 'payments')
            @include('livewire.partials.tables.payments')
        @endif

        {{-- ── Movements ───────────────────────────────── --}}
        @if($activeDataTab === 'movements')
            @include('livewire.partials.tables.movements')
        @endif

        {{-- ── Working Documents ───────────────────────── --}}
        @if($activeDataTab === 'working_documents')
            @include('livewire.partials.tables.working-documents')
        @endif

        {{-- ── Chart of Accounts ───────────────────────── --}}
        @if($activeDataTab === 'general_ledger_accounts')
            @include('livewire.partials.tables.general-ledger-accounts')
        @endif

        {{-- ── Ledger Entries ──────────────────────────── --}}
        @if($activeDataTab === 'general_ledger_entries')
            @include('livewire.partials.tables.general-ledger-entries')
        @endif

    </div>
</div>
