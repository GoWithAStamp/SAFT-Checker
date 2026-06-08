<?php

namespace App\Services\SaftValidator\Rules\MasterFiles;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

class MasterFilesValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $masterFiles = $this->xml->MasterFiles;

        if (!$masterFiles) {
            $this->addWarning('MASTER_FILES_MISSING', 'Secção MasterFiles em falta.', 'master_files');
            return $this->result();
        }

        $this->validateCustomers($masterFiles);
        $this->validateProducts($masterFiles);
        $this->validateTaxTable($masterFiles);
        $this->validateSuppliers($masterFiles);

        return $this->result();
    }

    protected function validateCustomers(\SimpleXMLElement $masterFiles): void
    {
        if (!isset($masterFiles->Customer)) {
            return;
        }

        $customerIDs = [];

        foreach ($masterFiles->Customer as $customer) {
            $customerID = (string) $customer->CustomerID;

            if (isset($customerIDs[$customerID])) {
                $this->addError(
                    'MASTER_CUSTOMER_DUPLICATE',
                    "Cliente duplicado: {$customerID}.",
                    'master_files',
                    'CustomerID'
                );
            }
            $customerIDs[$customerID] = true;

            $this->validateCustomerNIF($customer, $customerID);
        }

        $this->addInfo(
            'MASTER_CUSTOMERS_COUNT',
            "Total de clientes: " . count($customerIDs),
            'master_files'
        );
    }

    protected function validateCustomerNIF(\SimpleXMLElement $customer, string $customerID): void
    {
        $nif = $this->nodeValue($customer, 'CustomerTaxID');

        if ($nif === null) {
            $this->addError(
                'MASTER_CUSTOMER_NIF_MISSING',
                "NIF em falta para o cliente {$customerID}.",
                'master_files',
                'CustomerTaxID'
            );
            return;
        }

        if ($nif === '999999990') {
            return;
        }

        $country = $this->nodeValue($customer->BillingAddress ?? $customer, 'Country');
        if ($country === 'PT' && !$this->isValidNIF($nif)) {
            $this->addWarning(
                'MASTER_CUSTOMER_NIF_INVALID',
                "NIF possivelmente inválido para o cliente {$customerID}: {$nif}.",
                'master_files',
                'CustomerTaxID'
            );
        }
    }

    protected function validateProducts(\SimpleXMLElement $masterFiles): void
    {
        if (!isset($masterFiles->Product)) {
            return;
        }

        $productCodes = [];

        foreach ($masterFiles->Product as $product) {
            $code = (string) $product->ProductCode;

            if (isset($productCodes[$code])) {
                $this->addError(
                    'MASTER_PRODUCT_DUPLICATE',
                    "Produto duplicado: {$code}.",
                    'master_files',
                    'ProductCode'
                );
            }
            $productCodes[$code] = true;

            $productType = $this->nodeValue($product, 'ProductType');
            $validTypes = ['P', 'S', 'O', 'E', 'I'];
            if ($productType !== null && !in_array($productType, $validTypes)) {
                $this->addError(
                    'MASTER_PRODUCT_TYPE_INVALID',
                    "Tipo de produto inválido para {$code}: {$productType}.",
                    'master_files',
                    'ProductType'
                );
            }
        }

        $this->addInfo(
            'MASTER_PRODUCTS_COUNT',
            "Total de produtos/serviços: " . count($productCodes),
            'master_files'
        );
    }

    protected function validateTaxTable(\SimpleXMLElement $masterFiles): void
    {
        if (!isset($masterFiles->TaxTable)) {
            $this->addWarning('MASTER_TAX_TABLE_MISSING', 'TaxTable em falta.', 'master_files');
            return;
        }

        if (!isset($masterFiles->TaxTable->TaxTableEntry)) {
            $this->addWarning('MASTER_TAX_TABLE_EMPTY', 'TaxTable sem entradas.', 'master_files');
            return;
        }

        foreach ($masterFiles->TaxTable->TaxTableEntry as $entry) {
            $taxType = $this->nodeValue($entry, 'TaxType');
            $taxCode = $this->nodeValue($entry, 'TaxCode');
            $taxPercentage = $this->nodeValue($entry, 'TaxPercentage');
            $taxCountryRegion = $this->nodeValue($entry, 'TaxCountryRegion');

            if ($taxType === 'IVA' && $taxCountryRegion === 'PT') {
                $validRates = ['0', '6', '13', '23'];
                if ($taxPercentage !== null && !in_array($taxPercentage, $validRates)) {
                    $this->addWarning(
                        'MASTER_TAX_RATE_UNUSUAL',
                        "Taxa IVA invulgar para Portugal Continental: {$taxPercentage}% (código: {$taxCode}).",
                        'master_files',
                        'TaxPercentage',
                        'Taxas IVA em vigor: 0%, 6%, 13%, 23%.'
                    );
                }
            }
        }
    }

    protected function validateSuppliers(\SimpleXMLElement $masterFiles): void
    {
        if (!isset($masterFiles->Supplier)) {
            return;
        }

        $supplierIDs = [];

        foreach ($masterFiles->Supplier as $supplier) {
            $supplierID = (string) $supplier->SupplierID;

            if (isset($supplierIDs[$supplierID])) {
                $this->addError(
                    'MASTER_SUPPLIER_DUPLICATE',
                    "Fornecedor duplicado: {$supplierID}.",
                    'master_files',
                    'SupplierID'
                );
            }
            $supplierIDs[$supplierID] = true;
        }

        $this->addInfo(
            'MASTER_SUPPLIERS_COUNT',
            "Total de fornecedores: " . count($supplierIDs),
            'master_files'
        );
    }
}
