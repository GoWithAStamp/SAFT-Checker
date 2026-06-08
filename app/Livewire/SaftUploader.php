<?php

namespace App\Livewire;

use App\Services\SaftValidator\SaftValidatorService;
use App\Services\SaftValidator\ValidationResult;
use Livewire\Component;
use Livewire\WithFileUploads;

class SaftUploader extends Component
{
    use WithFileUploads;

    public $saftFile;
    public ?array $result = null;
    public ?array $summary = null;
    public bool $isValidating = false;
    public ?string $fileName = null;
    public ?string $errorMessage = null;
    public string $activeTab = 'all';

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
        $this->fileName = null;
        $this->errorMessage = null;
        $this->activeTab = 'all';
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.saft-uploader');
    }
}
