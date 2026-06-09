# SAFT Checker

**SAFT-PT file validator for Portuguese tax compliance.**

Validate your SAF-T (PT) files — billing or accounting — against Portuguese tax rules. Upload an XML, get instant validation with detailed error reporting, and explore all data in searchable tables.

**Live demo:** [saft-checker.fly.dev](https://saft-checker.fly.dev)

---

## Features

- **Tax validation** — Document sequence, digital signatures (ATCUD), NIF check digits, section totals, and line-level math verified automatically
- **Billing & Accounting** — Supports both SAFT types: billing (invoices, payments, transport docs, working docs) and accounting (chart of accounts, journal entries, general ledger)
- **Organised data** — Customers, products, invoices, payments, and ledger entries displayed in sortable, searchable tables
- **Chart of Accounts comparison** — Upload your own chart of accounts (Excel or CSV) and compare it against the SAFT's `GeneralLedgerAccounts`
- **Export** — Download any section as CSV or XML, or the entire file in one click
- **Bilingual** — Full Portuguese and English interface
- **Dark mode** — Light and dark themes
- **Privacy-first** — Files are processed on the server and deleted immediately. No data is stored.

## Tech Stack

- **PHP 8.4** / **Laravel 13** / **Livewire 4**
- **Tailwind CSS 4** with Apple Liquid Glass design
- **FrankenPHP** + Laravel Octane (production)
- **PhpSpreadsheet** for Excel/CSV parsing
- **Fly.io** deployment

## Getting Started

### Requirements

- PHP 8.4+
- Composer
- Node.js 20+

### Installation

```bash
git clone https://github.com/GoWithAStamp/SAFT-Checker.git
cd SAFT-Checker

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

php artisan serve
```

Open [localhost:8000](http://localhost:8000).

### Docker

```bash
docker build -t saft-checker .
docker run -p 8080:8080 \
  -e APP_KEY=base64:$(openssl rand -base64 32) \
  saft-checker
```

## Validation Rules

The validator checks against the **SAF-T (PT) v1.04_01** specification:

| Area | What's checked |
|------|----------------|
| **Header** | Required fields, NIF validity (mod-11), file version, date ranges, currency |
| **Master Files** | Duplicate customers/products/suppliers, NIF validation, product types, VAT rates |
| **Chart of Accounts** | SNC grouping categories (GR/GA/GM/AR/AA/AM), balance consistency, taxonomy codes |
| **Sales Invoices** | Number format, ATCUD, sequence gaps, document totals, line math, tax exemptions, customer cross-reference |
| **Payments** | Totals, status, invoice cross-references, date consistency |
| **Working Documents** | Status, dates, GrossTotal = NetTotal + TaxPayable |
| **Transport Documents** | Dates, origin/destination addresses |
| **Ledger Entries** | Double-entry balance (debits = credits), account cross-reference, period consistency |

## Project Structure

```
app/
├── Http/Middleware/SetLocale.php    # Language switcher middleware
├── Livewire/SaftUploader.php        # Main upload + results component
└── Services/
    ├── PlanoContasComparator.php     # Chart of accounts comparison
    └── SaftValidator/
        ├── SaftValidatorService.php  # Validation orchestrator
        ├── SaftDataExtractor.php     # XML → structured arrays
        ├── SaftExportService.php     # CSV/XML export
        ├── ValidationResult.php      # Result value object
        ├── ValidationError.php       # Error/warning/info value object
        ├── Parsers/SaftParser.php    # XML parser with namespace support
        └── Rules/
            ├── BaseValidator.php     # Abstract base with NIF validation
            ├── Header/
            ├── MasterFiles/
            └── SourceDocuments/
```

## License

MIT
