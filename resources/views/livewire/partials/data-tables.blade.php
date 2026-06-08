<div>
    {{-- Data Sub-tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $dataTabs = [
                'header' => ['label' => __('saft.data_header'), 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                'customers' => ['label' => __('saft.data_customers') . ' (' . count($saftData['customers']) . ')', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                'products' => ['label' => __('saft.data_products') . ' (' . count($saftData['products']) . ')', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                'tax_table' => ['label' => __('saft.data_tax_table') . ' (' . count($saftData['tax_table']) . ')', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                'invoices' => ['label' => __('saft.data_invoices') . ' (' . count($saftData['invoices']) . ')', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'payments' => ['label' => __('saft.data_payments') . ' (' . count($saftData['payments']) . ')', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                'movements' => ['label' => __('saft.data_movements') . ' (' . count($saftData['movements']) . ')', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                'working_documents' => ['label' => __('saft.data_working_docs') . ' (' . count($saftData['working_documents']) . ')', 'icon' => 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2'],
            ];
            if (empty($saftData['suppliers'])) unset($dataTabs['suppliers']);
        @endphp

        @foreach($dataTabs as $key => $tab)
            <button
                wire:click="setActiveDataTab('{{ $key }}')"
                class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $activeDataTab === $key ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                </svg>
                {{ $tab['label'] }}
            </button>
        @endforeach

        @if(!empty($saftData['suppliers']))
            <button
                wire:click="setActiveDataTab('suppliers')"
                class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ $activeDataTab === 'suppliers' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
            >
                {{ __('saft.data_suppliers') }} ({{ count($saftData['suppliers']) }})
            </button>
        @endif
    </div>

    {{-- Export Buttons --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('saft.export_section') }}:</span>
            <button wire:click="exportCsv('{{ $activeDataTab }}')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                CSV
            </button>
            <button wire:click="exportXml('{{ $activeDataTab }}')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-800 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                XML
            </button>
        </div>
        <button wire:click="exportAllCsv" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ __('saft.export_all') }}
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- Header --}}
        @if($activeDataTab === 'header' && !empty($saftData['header']))
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('saft.header_title') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        ];
                    @endphp
                    @foreach($headerLabels as $key => $label)
                        @if(!empty($saftData['header'][$key]))
                            <div class="border border-gray-100 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-900/50">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $saftData['header'][$key] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Customers --}}
        @if($activeDataTab === 'customers')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_id') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_nif') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_address') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_city') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_postal_code') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_country') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['customers'] as $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $customer['CustomerID'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-700 dark:text-gray-300">{{ $customer['CustomerTaxID'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $customer['CompanyName'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $customer['AddressDetail'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $customer['City'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $customer['PostalCode'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $customer['Country'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_customers') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Suppliers --}}
        @if($activeDataTab === 'suppliers')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_id') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_nif') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_address') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_city') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_country') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['suppliers'] ?? [] as $supplier)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $supplier['SupplierID'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-700 dark:text-gray-300">{{ $supplier['SupplierTaxID'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $supplier['CompanyName'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier['AddressDetail'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier['City'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier['Country'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_suppliers') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Products --}}
        @if($activeDataTab === 'products')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_code') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_group') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_description') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_barcode') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['products'] as $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $product['ProductType'] === 'P' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300' :
                                           ($product['ProductType'] === 'S' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' :
                                           ($product['ProductType'] === 'I' ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                                        {{ __('saft.product_type_' . $product['ProductType']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $product['ProductCode'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $product['ProductGroup'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product['ProductDescription'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $product['ProductNumberCode'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_products') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Tax Table --}}
        @if($activeDataTab === 'tax_table')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_region') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_code') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_description') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_rate') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_validity') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['tax_table'] as $tax)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $tax['TaxType'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tax['TaxCountryRegion'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-700 dark:text-gray-300">{{ $tax['TaxCode'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tax['Description'] }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ $tax['TaxPercentage'] }}%</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $tax['TaxExpirationDate'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_tax_table') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Invoices --}}
        @if($activeDataTab === 'invoices')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_invoice_no') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_atcud') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_customer') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_net') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_tax') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_total') }}</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['invoices'] as $invoice)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer" wire:click="toggleInvoice('{{ $invoice['InvoiceNo'] }}')">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $invoice['InvoiceNo'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $invoice['ATCUD'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                        {{ $invoice['InvoiceType'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $invoice['InvoiceStatus'] === 'N' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' :
                                           ($invoice['InvoiceStatus'] === 'A' ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' :
                                           ($invoice['InvoiceStatus'] === 'F' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                                        {{ match($invoice['InvoiceStatus']) {
                                            'N' => __('saft.status_normal'),
                                            'S' => __('saft.status_self_billing'),
                                            'A' => __('saft.status_cancelled'),
                                            'R' => __('saft.status_summary'),
                                            'F' => __('saft.status_billed'),
                                            default => $invoice['InvoiceStatus']
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice['InvoiceDate'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice['CustomerID'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$invoice['NetTotal'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$invoice['TaxPayable'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ number_format((float)$invoice['GrossTotal'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5 transition-transform {{ $expandedInvoice === $invoice['InvoiceNo'] ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </td>
                            </tr>
                            {{-- Expanded Lines --}}
                            @if($expandedInvoice === $invoice['InvoiceNo'] && !empty($invoice['Lines']))
                                <tr>
                                    <td colspan="10" class="px-0 py-0">
                                        <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-b border-gray-100 dark:border-gray-700">
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('saft.invoice_lines') }}</p>
                                            <table class="min-w-full">
                                                <thead>
                                                    <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                                        <th class="pr-4 py-1 text-left">{{ __('saft.col_line_number') }}</th>
                                                        <th class="pr-4 py-1 text-left">{{ __('saft.col_code') }}</th>
                                                        <th class="pr-4 py-1 text-left">{{ __('saft.col_description') }}</th>
                                                        <th class="pr-4 py-1 text-right">{{ __('saft.col_quantity') }}</th>
                                                        <th class="pr-4 py-1 text-right">{{ __('saft.col_unit_price') }}</th>
                                                        <th class="pr-4 py-1 text-right">{{ __('saft.col_amount') }}</th>
                                                        <th class="pr-4 py-1 text-right">{{ __('saft.col_tax') }}</th>
                                                        <th class="pr-4 py-1 text-left">{{ __('saft.col_exemption') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($invoice['Lines'] as $line)
                                                        <tr class="text-sm border-t border-gray-200 dark:border-gray-700">
                                                            <td class="pr-4 py-2 text-gray-500 dark:text-gray-400">{{ $line['LineNumber'] }}</td>
                                                            <td class="pr-4 py-2 font-mono text-gray-700 dark:text-gray-300">{{ $line['ProductCode'] }}</td>
                                                            <td class="pr-4 py-2 text-gray-700 dark:text-gray-300">{{ $line['ProductDescription'] }}</td>
                                                            <td class="pr-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ $line['Quantity'] }}</td>
                                                            <td class="pr-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ $line['UnitPrice'] ? number_format((float)$line['UnitPrice'], 2, ',', '.') . ' €' : '-' }}</td>
                                                            <td class="pr-4 py-2 text-right font-medium text-gray-900 dark:text-white">{{ ($line['CreditAmount'] ?: $line['DebitAmount']) ? number_format((float)($line['CreditAmount'] ?: $line['DebitAmount']), 2, ',', '.') . ' €' : '-' }}</td>
                                                            <td class="pr-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ $line['TaxPercentage'] ? $line['TaxPercentage'] . '%' : '-' }}</td>
                                                            <td class="pr-4 py-2 text-gray-500 dark:text-gray-400 text-xs">{{ $line['TaxExemptionReason'] ?: ($line['TaxExemptionCode'] ?: '-') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_invoices') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Payments --}}
        @if($activeDataTab === 'payments')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_reference') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_atcud') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_customer') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_net') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_tax') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['payments'] as $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $payment['PaymentRefNo'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $payment['ATCUD'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment['PaymentType'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $payment['PaymentStatus'] === 'N' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' }}">
                                        {{ $payment['PaymentStatus'] === 'N' ? __('saft.status_normal') : ($payment['PaymentStatus'] === 'A' ? __('saft.status_cancelled') : $payment['PaymentStatus']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment['TransactionDate'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $payment['CustomerID'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$payment['NetTotal'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$payment['TaxPayable'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ number_format((float)$payment['GrossTotal'], 2, ',', '.') }} &euro;</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_payments') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Movement of Goods --}}
        @if($activeDataTab === 'movements')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_document') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_atcud') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_customer') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_origin') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_destination') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['movements'] as $movement)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $movement['DocumentNumber'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $movement['ATCUD'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement['MovementType'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $movement['MovementStatus'] === 'N' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' }}">
                                        {{ $movement['MovementStatus'] === 'N' ? __('saft.status_normal') : ($movement['MovementStatus'] === 'A' ? __('saft.status_cancelled') : $movement['MovementStatus']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement['MovementDate'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $movement['CustomerID'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $movement['ShipFrom'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $movement['ShipTo'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_movements') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Working Documents --}}
        @if($activeDataTab === 'working_documents')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_document') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_atcud') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_customer') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_net') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_tax') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('saft.col_total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($saftData['working_documents'] as $doc)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $doc['DocumentNumber'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $doc['ATCUD'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $doc['WorkType'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $doc['WorkStatus'] === 'N' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' }}">
                                        {{ $doc['WorkStatus'] === 'N' ? __('saft.status_normal') : ($doc['WorkStatus'] === 'A' ? __('saft.status_cancelled') : $doc['WorkStatus']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $doc['WorkDate'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $doc['CustomerID'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$doc['NetTotal'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format((float)$doc['TaxPayable'], 2, ',', '.') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">{{ number_format((float)$doc['GrossTotal'], 2, ',', '.') }} &euro;</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">{{ __('saft.empty_working_docs') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
