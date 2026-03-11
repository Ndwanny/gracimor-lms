<?php

namespace App\Http\Resources;

class _OverdueControllerIntegration
{
    public function logContact($request): JsonResponse
    {
        $log = $loan->reminders()->create([...]);

        // BEFORE: return response()->json(['message' => '...', 'log' => $log], 201);
        return response()->json([
            'message' => 'Contact attempt logged.',
            'log'     => \App\Http\Resources\ReminderResource::make($log),
        ], 201);
    }

    // stats, loans, collectionsQueue, escalate — keep as plain JSON
    // (they return aggregate/computed data, not model instances)
}


/*
|=============================================================================
| FILE PLACEMENT (one class per file in production)
|=============================================================================
|
| app/Http/Resources/GracimorResource.php              ← abstract base
|
| app/Http/Resources/BorrowerResource.php
| app/Http/Resources/BorrowerCollection.php
|
| app/Http/Resources/LoanResource.php
| app/Http/Resources/LoanCollection.php
| app/Http/Resources/LoanBalanceResource.php
| app/Http/Resources/LoanScheduleResource.php
| app/Http/Resources/LoanStatusHistoryResource.php
|
| app/Http/Resources/PaymentResource.php
| app/Http/Resources/PenaltyResource.php
| app/Http/Resources/CollateralAssetResource.php
| app/Http/Resources/GuarantorResource.php
| app/Http/Resources/LoanProductResource.php
|
| app/Http/Resources/UserResource.php
| app/Http/Resources/AuditLogResource.php
| app/Http/Resources/ReminderResource.php
|
|=============================================================================
| JSON RESPONSE SHAPE EXAMPLES
|=============================================================================
|
| Single resource (show / store / update):
| ─────────────────────────────────────────
| {
|   "data": {
|     "id": 42,
|     "loan_number": "GRS-2026-00042",
|     "status": { "value": "overdue", "label": "Overdue", "colour": "red" },
|     "financials": {
|       "principal_amount": 50000.00,
|       "interest_rate": 28.00,
|       "monthly_instalment": 5231.44,
|       ...
|     },
|     "balance": {
|       "total_outstanding": 38200.00,
|       "days_overdue": 14,
|       ...
|     },
|     ...
|   }
| }
|
| Paginated collection (index):
| ─────────────────────────────────────────
| {
|   "data": [ { ... }, { ... } ],
|   "aggregate": {
|     "total": 214,
|     "active": 156,
|     "overdue": 38,
|     "pending": 12,
|     "total_outstanding": 2940199.00
|   },
|   "meta": {
|     "current_page": 1,
|     "last_page": 11,
|     "per_page": 20,
|     "total": 214
|   },
|   "links": {
|     "first": "/api/loans?page=1",
|     "last": "/api/loans?page=11",
|     "prev": null,
|     "next": "/api/loans?page=2"
|   }
| }
|
| Role-gated fields (example — officer vs manager):
| ─────────────────────────────────────────
| Officer sees:
|   { ..., "notes": null, "internal_notes": null, "ip_address": null }
|
| Manager sees:
|   { ..., "notes": { "loan_purpose": "...", "approval_notes": "..." },
|           "internal_notes": "KYC doc expires Jan 2027...",
|           "ip_address": "196.32.44.1" }
|
| Relation not loaded (whenLoaded — no N+1):
| ─────────────────────────────────────────
| If 'penalties' not eager-loaded, the key is omitted entirely.
| If loaded: "penalties": [ { "id": 1, "amount": 250.00, ... } ]
|
*/
