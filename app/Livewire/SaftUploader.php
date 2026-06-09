<?php

namespace App\Livewire;

use App\Services\SaftValidator\SaftValidatorService;
use App\Services\SaftValidator\SaftDataExtractor;
use App\Services\SaftValidator\SaftExportService;
use App\Services\PlanoContasComparator;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaftUploader extends Component
{
    use WithFileUploads;

    public $saftFile;
    public ?array $result = null;
    public ?array $summary = null;
    public ?array $saftData = null;
    public bool $isValidating = false;
    public ?string $fileName = null;
    public ?string $errorMessage = null;
    public string $activeTab = 'validation';
    public string $activeDataTab = 'header';
    public ?string $expandedInvoice = null;
    public ?string $expandedPayment = null;
    public ?string $expandedTransaction = null;
    public bool $isAccountingSaft = false;
    public string $saftType = 'billing';
    public ?string $searchQuery = null;
    public ?string $validationFilter = null;
    public $planoFile;
    public ?array $planoComparison = null;
    public ?string $planoFileName = null;
    public ?string $planoError = null;
    public string $planoFilter = 'all';

    public function updatedSaftFile(): void
    {
        $this->validate([
            'saftFile' => ['required', 'file', 'max:204800'],
        ], [
            'saftFile.required' => 'Por favor selecione um ficheiro SAFT-PT.',
            'saftFile.max' => 'O ficheiro não pode exceder 200MB.',
        ]);

        $extension = $this->saftFile->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['xml'])) {
            $this->errorMessage = 'O ficheiro deve ser um XML.';
            $this->saftFile = null;
            return;
        }

        $this->fileName = $this->saftFile->getClientOriginalName();
        $this->errorMessage = null;
    }

    public function validate_saft(): void
    {
        if (!$this->saftFile) {
            $this->errorMessage = 'Por favor selecione um ficheiro SAFT-PT.';
            return;
        }

        $this->isValidating = true;
        $this->result = null;
        $this->summary = null;
        $this->saftData = null;
        $this->errorMessage = null;

        try {
            $path = $this->saftFile->getRealPath();

            $validator = new SaftValidatorService();
            $validationResult = $validator->validate($path);

            $this->result = [
                'errors' => array_map(fn($e) => (array) $e, $validationResult->errors),
                'warnings' => array_map(fn($w) => (array) $w, $validationResult->warnings),
                'info' => array_map(fn($i) => (array) $i, $validationResult->info),
            ];
            $this->summary = $validationResult->summary();

            $extractor = SaftDataExtractor::fromFile($path);
            $this->saftData = $extractor->extractAll();
            $this->isAccountingSaft = $extractor->isAccountingSaft();
        } catch (\Throwable $e) {
            $this->errorMessage = __('saft.upload_error_generic') . $e->getMessage();
        } finally {
            $this->isValidating = false;
            $this->cleanupTempFile();
        }
    }

    public function loadSample(): void
    {
        $file = $this->saftType === 'accounting'
            ? 'tests/fixtures/sample_saft_accounting.xml'
            : 'tests/fixtures/sample_saft.xml';

        $path = base_path($file);

        if (!file_exists($path)) {
            $this->errorMessage = 'Sample file not found.';
            return;
        }

        $this->isValidating = true;
        $this->result = null;
        $this->summary = null;
        $this->saftData = null;
        $this->errorMessage = null;

        try {
            $this->fileName = $this->saftType === 'accounting'
                ? 'sample_contabilidade.xml'
                : 'sample_faturacao.xml';

            $validator = new SaftValidatorService();
            $validationResult = $validator->validate($path);

            $this->result = [
                'errors' => array_map(fn($e) => (array) $e, $validationResult->errors),
                'warnings' => array_map(fn($w) => (array) $w, $validationResult->warnings),
                'info' => array_map(fn($i) => (array) $i, $validationResult->info),
            ];
            $this->summary = $validationResult->summary();

            $extractor = SaftDataExtractor::fromFile($path);
            $this->saftData = $extractor->extractAll();
            $this->isAccountingSaft = $extractor->isAccountingSaft();
        } catch (\Throwable $e) {
            $this->errorMessage = __('saft.upload_error_generic') . $e->getMessage();
        } finally {
            $this->isValidating = false;
        }
    }

    public function setSaftType(string $type): void
    {
        $this->saftType = $type;
    }

    public function setValidationFilter(?string $filter): void
    {
        $this->validationFilter = $this->validationFilter === $filter ? null : $filter;
    }

    public function resetUpload(): void
    {
        $this->cleanupTempFile();
        $this->result = null;
        $this->summary = null;
        $this->saftData = null;
        $this->fileName = null;
        $this->errorMessage = null;
        $this->activeTab = 'validation';
        $this->activeDataTab = 'header';
        $this->expandedInvoice = null;
        $this->expandedPayment = null;
        $this->expandedTransaction = null;
        $this->isAccountingSaft = false;
        $this->saftType = 'billing';
        $this->searchQuery = null;
        $this->validationFilter = null;
        $this->planoComparison = null;
        $this->planoFileName = null;
        $this->planoError = null;
        $this->planoFilter = 'all';
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setActiveDataTab(string $tab): void
    {
        $this->activeDataTab = $tab;
        $this->expandedInvoice = null;
        $this->expandedPayment = null;
        $this->expandedTransaction = null;
    }

    public function toggleTransaction(string $transactionID): void
    {
        $this->expandedTransaction = $this->expandedTransaction === $transactionID ? null : $transactionID;
    }

    public function toggleInvoice(string $invoiceNo): void
    {
        $this->expandedInvoice = $this->expandedInvoice === $invoiceNo ? null : $invoiceNo;
    }

    public function updatedPlanoFile(): void
    {
        $this->planoError = null;
        $this->planoComparison = null;

        if (!$this->planoFile) return;

        $extension = strtolower($this->planoFile->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $this->planoError = __('saft.plano_invalid_format');
            $this->planoFile = null;
            return;
        }

        $this->planoFileName = $this->planoFile->getClientOriginalName();

        try {
            $path = $this->planoFile->getRealPath();
            $planoAccounts = PlanoContasComparator::parse($path, $extension);

            if (empty($planoAccounts)) {
                $this->planoError = __('saft.plano_empty');
                return;
            }

            $saftAccounts = $this->saftData['general_ledger_accounts'] ?? [];
            if (empty($saftAccounts)) {
                $this->planoError = __('saft.plano_no_saft_accounts');
                return;
            }

            $this->planoComparison = PlanoContasComparator::compare($planoAccounts, $saftAccounts);
        } catch (\Throwable $e) {
            $this->planoError = __('saft.plano_parse_error') . ' ' . $e->getMessage();
        } finally {
            $this->planoFile = null;
        }
    }

    public function setPlanoFilter(string $filter): void
    {
        $this->planoFilter = $filter;
    }

    public function clearPlanoComparison(): void
    {
        $this->planoComparison = null;
        $this->planoFileName = null;
        $this->planoError = null;
        $this->planoFilter = 'all';
    }

    public function togglePayment(string $paymentNo): void
    {
        $this->expandedPayment = $this->expandedPayment === $paymentNo ? null : $paymentNo;
    }

    public function exportCsv(string $section): StreamedResponse
    {
        $baseName = pathinfo($this->fileName ?? 'saft', PATHINFO_FILENAME);
        $filename = "{$baseName}_{$section}.csv";

        return response()->streamDownload(function () use ($section) {
            if ($section === 'header') {
                echo SaftExportService::headerToCsv($this->saftData['header']);
            } else {
                $headers = SaftExportService::getHeaders($section);
                echo SaftExportService::toCsv($this->saftData[$section] ?? [], $headers);
            }
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportXml(string $section): StreamedResponse
    {
        $baseName = pathinfo($this->fileName ?? 'saft', PATHINFO_FILENAME);
        $filename = "{$baseName}_{$section}.xml";

        $itemNames = [
            'customers' => ['Customers', 'Customer'],
            'suppliers' => ['Suppliers', 'Supplier'],
            'products' => ['Products', 'Product'],
            'tax_table' => ['TaxTable', 'TaxTableEntry'],
            'invoices' => ['SalesInvoices', 'Invoice'],
            'payments' => ['Payments', 'Payment'],
            'movements' => ['MovementOfGoods', 'StockMovement'],
            'working_documents' => ['WorkingDocuments', 'WorkDocument'],
            'general_ledger_accounts' => ['GeneralLedgerAccounts', 'Account'],
            'general_ledger_entries' => ['GeneralLedgerEntries', 'Transaction'],
        ];

        return response()->streamDownload(function () use ($section, $itemNames) {
            if ($section === 'header') {
                echo SaftExportService::headerToXml($this->saftData['header']);
            } else {
                $names = $itemNames[$section] ?? [$section, 'Item'];
                $headers = SaftExportService::getHeaders($section);
                echo SaftExportService::toXml($this->saftData[$section] ?? [], $names[0], $names[1], $headers);
            }
        }, $filename, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function exportAllCsv(): StreamedResponse
    {
        $baseName = pathinfo($this->fileName ?? 'saft', PATHINFO_FILENAME);
        $filename = "{$baseName}_completo.csv";

        $sections = ['customers', 'suppliers', 'products', 'tax_table', 'general_ledger_accounts', 'invoices', 'payments', 'movements', 'working_documents', 'general_ledger_entries'];
        $sectionLabels = [
            'customers' => 'CLIENTES',
            'suppliers' => 'FORNECEDORES',
            'products' => 'PRODUTOS',
            'tax_table' => 'TABELA IVA',
            'general_ledger_accounts' => 'PLANO DE CONTAS',
            'invoices' => 'FATURAS',
            'payments' => 'PAGAMENTOS',
            'movements' => 'DOCUMENTOS DE TRANSPORTE',
            'working_documents' => 'DOCUMENTOS DE TRABALHO',
            'general_ledger_entries' => 'LANÇAMENTOS CONTABILÍSTICOS',
        ];

        return response()->streamDownload(function () use ($sections, $sectionLabels) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['=== CABEÇALHO ==='], ';', '"', "\\");
            fputcsv($output, ['Campo', 'Valor'], ';', '"', "\\");
            foreach ($this->saftData['header'] as $key => $value) {
                if (!empty($value)) {
                    fputcsv($output, [$key, $value], ';', '"', "\\");
                }
            }
            fputcsv($output, [], ';', '"', "\\");

            foreach ($sections as $section) {
                $data = $this->saftData[$section] ?? [];
                if (empty($data)) continue;

                $headers = SaftExportService::getHeaders($section);
                fputcsv($output, ["=== {$sectionLabels[$section]} ==="], ';', '"', "\\");
                fputcsv($output, array_values($headers), ';', '"', "\\");

                foreach ($data as $row) {
                    $csvRow = [];
                    foreach (array_keys($headers) as $key) {
                        $value = $row[$key] ?? '';
                        if (is_array($value)) {
                            $value = count($value) . ' linhas';
                        }
                        $csvRow[] = $value;
                    }
                    fputcsv($output, $csvRow, ';', '"', "\\");
                }
                fputcsv($output, [], ';', '"', "\\");
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * GDPR: Delete temporary uploaded file immediately after processing.
     */
    protected function cleanupTempFile(): void
    {
        try {
            if ($this->saftFile && method_exists($this->saftFile, 'getRealPath')) {
                $realPath = $this->saftFile->getRealPath();
                if ($realPath && file_exists($realPath)) {
                    @unlink($realPath);
                }
            }

            // Also clean Livewire's temp storage path
            if ($this->saftFile && method_exists($this->saftFile, 'getFilename')) {
                $tmpPath = 'livewire-tmp/' . $this->saftFile->getFilename();
                if (Storage::disk('local')->exists($tmpPath)) {
                    Storage::disk('local')->delete($tmpPath);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — file will be cleaned by Livewire's 24h cleanup
        }

        $this->saftFile = null;
    }

    public function render()
    {
        return view('livewire.saft-uploader');
    }
}
