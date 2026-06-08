<?php

namespace App\Livewire;

use App\Services\SaftValidator\SaftValidatorService;
use App\Services\SaftValidator\SaftDataExtractor;
use App\Services\SaftValidator\SaftExportService;
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
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erro ao validar o ficheiro: ' . $e->getMessage();
        } finally {
            $this->isValidating = false;
        }
    }

    public function resetUpload(): void
    {
        $this->saftFile = null;
        $this->result = null;
        $this->summary = null;
        $this->saftData = null;
        $this->fileName = null;
        $this->errorMessage = null;
        $this->activeTab = 'validation';
        $this->activeDataTab = 'header';
        $this->expandedInvoice = null;
        $this->expandedPayment = null;
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
    }

    public function toggleInvoice(string $invoiceNo): void
    {
        $this->expandedInvoice = $this->expandedInvoice === $invoiceNo ? null : $invoiceNo;
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

        $sections = ['customers', 'suppliers', 'products', 'tax_table', 'invoices', 'payments', 'movements', 'working_documents'];
        $sectionLabels = [
            'customers' => 'CLIENTES',
            'suppliers' => 'FORNECEDORES',
            'products' => 'PRODUTOS',
            'tax_table' => 'TABELA IVA',
            'invoices' => 'FATURAS',
            'payments' => 'PAGAMENTOS',
            'movements' => 'DOCUMENTOS DE TRANSPORTE',
            'working_documents' => 'DOCUMENTOS DE TRABALHO',
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

    public function render()
    {
        return view('livewire.saft-uploader');
    }
}
