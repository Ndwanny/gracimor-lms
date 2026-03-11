<?php

return [

    // ── Company identity (used in emails, SMS, PDF headers) ───────────────────

    'company_name'    => env('GRACIMOR_COMPANY_NAME', 'Gracimor Microfinance Ltd'),
    'company_address' => env('GRACIMOR_ADDRESS',      'Plot 12345, Cairo Road, Lusaka, Zambia'),
    'office_phone'    => env('GRACIMOR_OFFICE_PHONE', '+260211000001'),
    'company_email'   => env('GRACIMOR_EMAIL',        'info@gracimor.co.zm'),

    // Bank of Zambia and ZRA registration (shown in email footers)
    'boz_licence'     => env('GRACIMOR_BOZ_LICENCE', 'MF/2019/001'),
    'tpin'            => env('GRACIMOR_TPIN',         '1000000000'),

    // ── Business rules ────────────────────────────────────────────────────────

    // Working days after full repayment before collateral is formally released
    'collateral_release_days' => (int) env('GRACIMOR_COLLATERAL_RELEASE_DAYS', 7),

    // Minimum days overdue before a loan can be written off (PAR 90 threshold)
    'write_off_min_days_overdue' => (int) env('GRACIMOR_WRITE_OFF_MIN_DAYS', 90),

    // ── Reporting ─────────────────────────────────────────────────────────────

    // Comma-separated email addresses for the daily portfolio digest
    // e.g. "ceo@gracimor.co.zm,manager.banda@gracimor.co.zm"
    'daily_report_recipients' => env('DAILY_REPORT_RECIPIENTS', ''),

    // ── Africa's Talking SMS + WhatsApp ──────────────────────────────────────

    'at_username'   => env('AT_USERNAME',  'sandbox'),
    'at_api_key'    => env('AT_API_KEY',   ''),
    'sms_sender_id' => env('AT_SENDER_ID', 'GRACIMOR'),

    // WhatsApp product name configured in your AT dashboard (Applications → WhatsApp)
    'at_wa_product' => env('AT_WA_PRODUCT', ''),

    // Set to true to route all SMS through the AT sandbox (no real messages sent)
    'at_sandbox'    => (bool) env('AT_SANDBOX', false),

    // How long to cache SMS templates in seconds (10 minutes)
    'sms_template_cache_ttl' => (int) env('SMS_TEMPLATE_CACHE_TTL', 600),

    // ── Loan calculator defaults ──────────────────────────────────────────────

    // Used by LoanCalculatorService when no product is specified
    'default_interest_method' => env('DEFAULT_INTEREST_METHOD', 'reducing_balance'),

    // ── Audit log ─────────────────────────────────────────────────────────────

    // How many days of audit logs to retain (730 = 2 years)
    'audit_log_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 730),

    // ── File uploads ──────────────────────────────────────────────────────────

    // Maximum document upload size in kilobytes (10 MB)
    'max_upload_kb' => (int) env('MAX_UPLOAD_KB', 10240),

    // Allowed MIME types for KYC document uploads
    'allowed_document_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],

];
