<?php

namespace App\Http\Controllers\Api;

use App\Models\SmsTemplate;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsTemplateController extends \App\Http\Controllers\Controller
{
    public function __construct(private readonly ReminderService $reminderService) {}

    // ── GET /api/sms-templates ────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $templates = SmsTemplate::orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => $this->format($t));

        return response()->json([
            'templates' => $templates,
            'categories' => [
                'payment'  => 'Payment',
                'loan'     => 'Loan Lifecycle',
                'reminder' => 'Repayment Reminders',
                'overdue'  => 'Overdue Notices',
                'system'   => 'System',
            ],
        ]);
    }

    // ── GET /api/sms-templates/{template} ────────────────────────────────────
    public function show(SmsTemplate $template): JsonResponse
    {
        return response()->json($this->format($template, detailed: true));
    }

    // ── PUT /api/sms-templates/{template} ────────────────────────────────────
    public function update(Request $request, SmsTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:150',
            'body'      => 'sometimes|required|string|min:5|max:918',
            'is_active' => 'sometimes|boolean',
        ]);

        // Validate no unknown variables introduced
        if (isset($validated['body'])) {
            $unknown = $this->unknownVariables($validated['body']);
            if (!empty($unknown)) {
                return response()->json([
                    'message' => 'Template contains unknown variable(s): {' .
                                 implode('}, {', $unknown) . '}. ' .
                                 'Please use only supported variables.',
                    'unknown_variables' => $unknown,
                ], 422);
            }
        }

        $template->update(array_merge($validated, [
            'last_edited_by' => Auth::id(),
        ]));

        // Invalidate cached version
        $this->reminderService->flushTemplateCache($template->trigger_key);

        return response()->json([
            'message'  => 'Template updated.',
            'template' => $this->format($template->fresh(), detailed: true),
        ]);
    }

    // ── POST /api/sms-templates/{template}/preview ───────────────────────────
    // Returns rendered preview body using demo values (no SMS sent)
    public function preview(SmsTemplate $template): JsonResponse
    {
        return response()->json([
            'trigger_key'  => $template->trigger_key,
            'preview_body' => $template->previewBody(),
            'char_count'   => mb_strlen($template->previewBody()),
            'sms_pages'    => (int) ceil(mb_strlen($template->previewBody()) / 153),
            'max_length'   => $template->estimatedMaxLength(),
            'variables'    => $template->extractVariables(),
        ]);
    }

    // ── POST /api/sms-templates/flush-cache ──────────────────────────────────
    // Force-flushes all cached templates (useful after bulk import)
    public function flushCache(): JsonResponse
    {
        $this->reminderService->flushAllTemplateCache();
        return response()->json(['message' => 'SMS template cache flushed.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function format(SmsTemplate $t, bool $detailed = false): array
    {
        $base = [
            'id'          => $t->id,
            'trigger_key' => $t->trigger_key,
            'name'        => $t->name,
            'category'    => $t->category,
            'is_active'   => $t->is_active,
            'char_count'  => $t->char_count,
            'sms_pages'   => $t->sms_pages,
            'updated_at'  => $t->updated_at?->toIso8601String(),
        ];

        if ($detailed) {
            $base['body']       = $t->body;
            $base['variables']  = $t->extractVariables();
            $base['preview']    = $t->previewBody();
            $base['max_length'] = $t->estimatedMaxLength();
            $base['edited_by']  = $t->lastEditedBy?->name;
        }

        return $base;
    }

    /** Return any {variable} tokens not in the supported variable list */
    private function unknownVariables(string $body): array
    {
        $supported = [
            'first_name', 'last_name', 'loan_number', 'amount_due', 'total_due',
            'due_date', 'amount_paid', 'receipt', 'balance_due', 'instalment_no',
            'days_overdue', 'penalty_amount', 'penalty_rate', 'total_penalties',
            'officer_name', 'officer_phone', 'company_name', 'company_phone',
        ];

        preg_match_all('/\{([^}]+)\}/', $body, $matches);
        $found = $matches[1] ?? [];

        return array_values(array_diff($found, $supported));
    }
}
