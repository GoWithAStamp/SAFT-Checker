<?php

namespace App\Services\SaftValidator\Rules\Header;

use App\Services\SaftValidator\Rules\BaseValidator;
use App\Services\SaftValidator\ValidationResult;

/**
 * Validates the Header section of a SAFT-PT file against the v1.04_01 specification.
 *
 * Checks mandatory fields (CompanyID, NIF, FiscalYear, dates, currency),
 * verifies the AuditFileVersion matches 1.04_01, validates the company's NIF
 * using the mod-11 check digit algorithm, and ensures date ranges are consistent.
 */
class HeaderValidator extends BaseValidator
{
    public function validate(): ValidationResult
    {
        $header = $this->xml->Header;

        if (!$header) {
            $this->addError('HEADER_MISSING', 'Elemento Header em falta no ficheiro SAFT-PT.', 'header');
            return $this->result();
        }

        $this->validateAuditFileVersion($header);
        $this->validateCompanyID($header);
        $this->validateTaxRegistrationNumber($header);
        $this->validateTaxAccountingBasis($header);
        $this->validateCompanyName($header);
        $this->validateFiscalYear($header);
        $this->validateDates($header);
        $this->validateCurrency($header);
        $this->validateCompanyAddress($header);

        return $this->result();
    }

    protected function validateAuditFileVersion(\SimpleXMLElement $header): void
    {
        $version = $this->nodeValue($header, 'AuditFileVersion');

        if ($version === null) {
            $this->addError('HEADER_VERSION_MISSING', 'AuditFileVersion em falta.', 'header', 'AuditFileVersion');
            return;
        }

        if ($version !== '1.04_01') {
            $this->addWarning(
                'HEADER_VERSION_UNEXPECTED',
                "Versão do ficheiro: {$version}. Versão esperada: 1.04_01.",
                'header',
                'AuditFileVersion'
            );
        }
    }

    protected function validateCompanyID(\SimpleXMLElement $header): void
    {
        $companyID = $this->nodeValue($header, 'CompanyID');

        if ($companyID === null) {
            $this->addError('HEADER_COMPANY_ID_MISSING', 'CompanyID em falta.', 'header', 'CompanyID');
            return;
        }

        if (!preg_match('/^\d{9}$/', $companyID) && !preg_match('/^.+ \d{9}$/', $companyID)) {
            $this->addWarning(
                'HEADER_COMPANY_ID_FORMAT',
                'CompanyID deve conter o NIF ou código de conservatória seguido do NIF.',
                'header',
                'CompanyID'
            );
        }
    }

    protected function validateTaxRegistrationNumber(\SimpleXMLElement $header): void
    {
        $nif = $this->nodeValue($header, 'TaxRegistrationNumber');

        if ($nif === null) {
            $this->addError('HEADER_NIF_MISSING', 'TaxRegistrationNumber em falta.', 'header', 'TaxRegistrationNumber');
            return;
        }

        if (!$this->isValidNIF($nif)) {
            $this->addError(
                'HEADER_NIF_INVALID',
                "NIF inválido: {$nif}. O NIF não passa na validação do dígito de controlo.",
                'header',
                'TaxRegistrationNumber'
            );
        }
    }

    protected function validateTaxAccountingBasis(\SimpleXMLElement $header): void
    {
        $basis = $this->nodeValue($header, 'TaxAccountingBasis');

        if ($basis === null) {
            $this->addError('HEADER_TAX_BASIS_MISSING', 'TaxAccountingBasis em falta.', 'header', 'TaxAccountingBasis');
            return;
        }

        $validValues = ['C', 'E', 'F', 'I', 'P', 'R', 'S', 'T'];
        if (!in_array($basis, $validValues)) {
            $this->addError(
                'HEADER_TAX_BASIS_INVALID',
                "TaxAccountingBasis inválido: {$basis}. Valores permitidos: " . implode(', ', $validValues),
                'header',
                'TaxAccountingBasis'
            );
        }

        $this->addInfo(
            'HEADER_TAX_BASIS_INFO',
            "Tipo de ficheiro: {$basis} ({$this->taxBasisDescription($basis)})",
            'header',
            'TaxAccountingBasis'
        );
    }

    protected function validateCompanyName(\SimpleXMLElement $header): void
    {
        $name = $this->nodeValue($header, 'CompanyName');

        if ($name === null) {
            $this->addError('HEADER_COMPANY_NAME_MISSING', 'CompanyName em falta.', 'header', 'CompanyName');
        }
    }

    protected function validateFiscalYear(\SimpleXMLElement $header): void
    {
        $fiscalYear = $this->nodeValue($header, 'FiscalYear');

        if ($fiscalYear === null) {
            $this->addError('HEADER_FISCAL_YEAR_MISSING', 'FiscalYear em falta.', 'header', 'FiscalYear');
            return;
        }

        if (!preg_match('/^\d{4}$/', $fiscalYear)) {
            $this->addError('HEADER_FISCAL_YEAR_INVALID', "FiscalYear inválido: {$fiscalYear}.", 'header', 'FiscalYear');
        }
    }

    protected function validateDates(\SimpleXMLElement $header): void
    {
        $startDate = $this->nodeValue($header, 'StartDate');
        $endDate = $this->nodeValue($header, 'EndDate');
        $dateCreated = $this->nodeValue($header, 'DateCreated');

        if ($startDate === null) {
            $this->addError('HEADER_START_DATE_MISSING', 'StartDate em falta.', 'header', 'StartDate');
        }

        if ($endDate === null) {
            $this->addError('HEADER_END_DATE_MISSING', 'EndDate em falta.', 'header', 'EndDate');
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $this->addError(
                'HEADER_DATE_RANGE_INVALID',
                "StartDate ({$startDate}) é posterior a EndDate ({$endDate}).",
                'header',
                'StartDate/EndDate'
            );
        }

        if ($dateCreated === null) {
            $this->addError('HEADER_DATE_CREATED_MISSING', 'DateCreated em falta.', 'header', 'DateCreated');
        }
    }

    protected function validateCurrency(\SimpleXMLElement $header): void
    {
        $currency = $this->nodeValue($header, 'CurrencyCode');

        if ($currency === null) {
            $this->addError('HEADER_CURRENCY_MISSING', 'CurrencyCode em falta.', 'header', 'CurrencyCode');
            return;
        }

        if ($currency !== 'EUR') {
            $this->addWarning(
                'HEADER_CURRENCY_NOT_EUR',
                "Moeda: {$currency}. Esperado EUR para empresas portuguesas.",
                'header',
                'CurrencyCode'
            );
        }
    }

    protected function validateCompanyAddress(\SimpleXMLElement $header): void
    {
        $address = $header->CompanyAddress;

        if (!$address) {
            $this->addError('HEADER_ADDRESS_MISSING', 'CompanyAddress em falta.', 'header', 'CompanyAddress');
            return;
        }

        if ($this->nodeValue($address, 'Country') === null) {
            $this->addError('HEADER_ADDRESS_COUNTRY_MISSING', 'País da morada da empresa em falta.', 'header', 'CompanyAddress.Country');
        }

        if ($this->nodeValue($address, 'City') === null) {
            $this->addWarning('HEADER_ADDRESS_CITY_MISSING', 'Cidade da morada da empresa em falta.', 'header', 'CompanyAddress.City');
        }

        if ($this->nodeValue($address, 'PostalCode') === null) {
            $this->addWarning('HEADER_ADDRESS_POSTAL_MISSING', 'Código postal da empresa em falta.', 'header', 'CompanyAddress.PostalCode');
        }
    }

    /** Map TaxAccountingBasis code to its Portuguese description. */
    protected function taxBasisDescription(string $basis): string
    {
        return match ($basis) {
            'C' => 'Contabilidade',
            'E' => 'Faturação emitida por terceiros',
            'F' => 'Faturação',
            'I' => 'Contabilidade integrada com faturação',
            'P' => 'Faturação parcial',
            'R' => 'Recibos',
            'S' => 'Autofaturação',
            'T' => 'Documentos de transporte',
            default => 'Desconhecido',
        };
    }
}
