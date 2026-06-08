<?php

namespace App\Services\SaftValidator\Rules\SourceDocuments;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

class MovementOfGoodsValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $sourceDocuments = $this->xml->SourceDocuments;
        if (!$sourceDocuments || !isset($sourceDocuments->MovementOfGoods)) {
            return $this->result();
        }

        $movements = $sourceDocuments->MovementOfGoods;

        $this->validateTotals($movements);
        $this->validateStockMovements($movements);

        return $this->result();
    }

    protected function validateTotals(\SimpleXMLElement $movements): void
    {
        $numberOfEntries = $this->nodeValue($movements, 'NumberOfMovementLines');
        $totalQuantity = $this->nodeValue($movements, 'TotalQuantityIssued');

        $actualCount = isset($movements->StockMovement) ? count($movements->StockMovement) : 0;

        if ($numberOfEntries !== null && (int) $numberOfEntries !== $actualCount) {
            $this->addError(
                'MOV_GOODS_NUMBER_ENTRIES_MISMATCH',
                "NumberOfMovementLines ({$numberOfEntries}) não corresponde ao número real de movimentos ({$actualCount}).",
                'movement_of_goods',
                'NumberOfMovementLines'
            );
        }
    }

    protected function validateStockMovements(\SimpleXMLElement $movements): void
    {
        if (!isset($movements->StockMovement)) {
            return;
        }

        $documentNumbers = [];

        foreach ($movements->StockMovement as $movement) {
            $docNumber = (string) $movement->DocumentNumber;

            if (isset($documentNumbers[$docNumber])) {
                $this->addError(
                    'MOV_GOODS_DUPLICATE',
                    "Documento de transporte duplicado: {$docNumber}.",
                    'movement_of_goods',
                    'DocumentNumber'
                );
            }
            $documentNumbers[$docNumber] = true;

            $this->validateMovementDates($movement, $docNumber);
            $this->validateMovementAddresses($movement, $docNumber);
        }

        $this->addInfo(
            'MOV_GOODS_COUNT',
            "Total de documentos de transporte: " . count($documentNumbers),
            'movement_of_goods'
        );
    }

    protected function validateMovementDates(\SimpleXMLElement $movement, string $docNumber): void
    {
        $movementDate = $this->nodeValue($movement, 'MovementDate');
        $systemEntryDate = $this->nodeValue($movement, 'SystemEntryDate');

        if ($movementDate === null) {
            $this->addError(
                'MOV_GOODS_DATE_MISSING',
                "MovementDate em falta no documento {$docNumber}.",
                'movement_of_goods',
                'MovementDate'
            );
        }

        if ($systemEntryDate === null) {
            $this->addError(
                'MOV_GOODS_SYSTEM_DATE_MISSING',
                "SystemEntryDate em falta no documento {$docNumber}.",
                'movement_of_goods',
                'SystemEntryDate'
            );
        }
    }

    protected function validateMovementAddresses(\SimpleXMLElement $movement, string $docNumber): void
    {
        $movementType = $this->nodeValue($movement, 'MovementType');

        if (!isset($movement->ShipFrom) && !isset($movement->ShipTo)) {
            $this->addWarning(
                'MOV_GOODS_NO_ADDRESSES',
                "Sem moradas de origem/destino no documento {$docNumber}.",
                'movement_of_goods',
                'ShipFrom/ShipTo'
            );
        }
    }
}
