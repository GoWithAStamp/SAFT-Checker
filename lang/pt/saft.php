<?php

return [
    // Navigation
    'app_name' => 'SAFT Checker',
    'subtitle' => 'Validador SAFT-PT',
    'footer' => 'SAFT Checker — Validador de ficheiros SAFT-PT conforme legislação portuguesa',

    // Upload
    'upload_title' => 'Validar ficheiro SAFT-PT',
    'upload_description' => 'Carregue o seu ficheiro SAFT-PT para verificar a conformidade com a legislação portuguesa.',
    'upload_drag' => 'Arraste o ficheiro SAFT-PT aqui',
    'upload_or' => 'ou',
    'upload_select' => 'Selecionar ficheiro XML',
    'upload_select_another' => 'Escolher outro ficheiro',
    'upload_ready' => 'Ficheiro pronto para validação',
    'upload_validate' => 'Validar SAFT-PT',
    'upload_validating' => 'A validar...',
    'upload_error_xml' => 'O ficheiro deve ser um XML.',
    'upload_error_required' => 'Por favor selecione um ficheiro SAFT-PT.',
    'upload_error_max' => 'O ficheiro não pode exceder 200MB.',
    'upload_error_generic' => 'Erro ao validar o ficheiro: ',

    // Results
    'results_title' => 'Resultado da Validação',
    'results_new' => 'Nova validação',
    'results_file' => 'Ficheiro',
    'results_status' => 'Estado',
    'results_valid' => 'Válido',
    'results_invalid' => 'Com erros',
    'results_errors' => 'Erros',
    'results_warnings' => 'Avisos',
    'results_info' => 'Informações',
    'results_no_issues' => 'Ficheiro sem problemas detectados.',
    'results_field' => 'Campo',
    'results_xml_line' => 'Linha XML',

    // Tabs
    'tab_validation' => 'Validação',
    'tab_data' => 'Dados do Ficheiro',

    // Data tabs
    'data_header' => 'Cabeçalho',
    'data_customers' => 'Clientes',
    'data_suppliers' => 'Fornecedores',
    'data_products' => 'Produtos',
    'data_tax_table' => 'Tabela IVA',
    'data_invoices' => 'Faturas',
    'data_payments' => 'Pagamentos',
    'data_movements' => 'Transporte',
    'data_working_docs' => 'Doc. Trabalho',

    // Export
    'export_section' => 'Exportar secção atual',
    'export_all' => 'Exportar tudo (CSV)',

    // Header fields
    'header_title' => 'Informação do Cabeçalho',
    'header_version' => 'Versão do Ficheiro',
    'header_company_id' => 'ID da Empresa',
    'header_nif' => 'NIF',
    'header_tax_basis' => 'Base Contabilística',
    'header_company_name' => 'Nome da Empresa',
    'header_business_name' => 'Nome Comercial',
    'header_fiscal_year' => 'Ano Fiscal',
    'header_start_date' => 'Data Início',
    'header_end_date' => 'Data Fim',
    'header_currency' => 'Moeda',
    'header_date_created' => 'Data de Criação',
    'header_tax_entity' => 'Entidade Fiscal',
    'header_product_nif' => 'NIF Empresa Software',
    'header_software_cert' => 'Nº Certificado Software',
    'header_software' => 'Software',
    'header_software_version' => 'Versão Software',
    'header_telephone' => 'Telefone',
    'header_email' => 'Email',
    'header_website' => 'Website',
    'header_address' => 'Morada',
    'header_city' => 'Cidade',
    'header_postal_code' => 'Código Postal',
    'header_country' => 'País',

    // Table columns
    'col_id' => 'ID',
    'col_nif' => 'NIF',
    'col_name' => 'Nome',
    'col_address' => 'Morada',
    'col_city' => 'Cidade',
    'col_postal_code' => 'C. Postal',
    'col_country' => 'País',
    'col_type' => 'Tipo',
    'col_code' => 'Código',
    'col_group' => 'Grupo',
    'col_description' => 'Descrição',
    'col_barcode' => 'Código Barras',
    'col_region' => 'Região',
    'col_rate' => 'Taxa (%)',
    'col_validity' => 'Validade',
    'col_invoice_no' => 'Nº Fatura',
    'col_atcud' => 'ATCUD',
    'col_status' => 'Estado',
    'col_date' => 'Data',
    'col_customer' => 'Cliente',
    'col_net' => 'Base',
    'col_tax' => 'IVA',
    'col_total' => 'Total',
    'col_reference' => 'Referência',
    'col_document' => 'Documento',
    'col_origin' => 'Origem',
    'col_destination' => 'Destino',
    'col_line_number' => '#',
    'col_quantity' => 'Qtd',
    'col_unit_price' => 'Preço Unit.',
    'col_amount' => 'Valor',
    'col_exemption' => 'Isenção',

    // Invoice lines
    'invoice_lines' => 'Linhas da Fatura',

    // Statuses
    'status_normal' => 'Normal',
    'status_cancelled' => 'Anulada',
    'status_self_billing' => 'Autofaturação',
    'status_summary' => 'Resumo',
    'status_billed' => 'Faturada',

    // Product types
    'product_type_P' => 'Produto',
    'product_type_S' => 'Serviço',
    'product_type_O' => 'Outro',
    'product_type_E' => 'Imp. Especial',
    'product_type_I' => 'Imposto/Taxa',

    // Empty states
    'empty_customers' => 'Sem clientes',
    'empty_suppliers' => 'Sem fornecedores',
    'empty_products' => 'Sem produtos',
    'empty_tax_table' => 'Sem entradas na tabela de impostos',
    'empty_invoices' => 'Sem faturas',
    'empty_payments' => 'Sem pagamentos',
    'empty_movements' => 'Sem documentos de transporte',
    'empty_working_docs' => 'Sem documentos de trabalho',

    // Theme
    'theme_light' => 'Modo claro',
    'theme_dark' => 'Modo escuro',
    'language' => 'Idioma',
];
