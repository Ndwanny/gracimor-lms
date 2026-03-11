<?php

namespace App\Http\Requests\Borrower;

class UploadDocumentRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function rules(): array
    {
        return [
            'file'          => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',  // 10 MB
            ],
            'document_type' => [
                'required',
                'in:nrc_front,nrc_back,utility_bill,payslip,bank_statement,' .
                   'title_deed,vehicle_logbook,valuation_report,employment_letter,other',
            ],
            'expiry_date'   => ['nullable', 'date', 'after:today'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $borrower = $this->route('borrower');

            if (!$borrower) {
                return;
            }

            // NRC documents: only one of each type allowed — offer replacement message
            if (in_array($this->document_type, ['nrc_front', 'nrc_back'])) {
                $existing = $borrower->documents()
                    ->where('document_type', $this->document_type)
                    ->where('is_superseded', false)
                    ->exists();

                if ($existing) {
                    // This is not an error — allow upload and mark old as superseded
                    // We just add an informational note to the response (handled in controller)
                    // No error added here intentionally
                }
            }

            // Validate file is actually readable (not zero bytes)
            $file = $this->file('file');
            if ($file && $file->getSize() === 0) {
                $v->errors()->add('file', 'The uploaded file appears to be empty.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.required'       => 'Please select a file to upload.',
            'file.file'           => 'The upload must be a valid file.',
            'file.mimes'          => 'Documents must be PDF, JPG, or PNG format.',
            'file.max'            => 'File size must not exceed 10 MB.',
            'document_type.required' => 'Please specify the document type.',
            'document_type.in'    => 'Please select a valid document type from the list.',
            'expiry_date.after'   => 'Expiry date must be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => 'document type',
            'expiry_date'   => 'expiry date',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// EscalateLoanRequest
// POST /api/overdue/escalate/{loan}
// ═══════════════════════════════════════════════════════════════════════════════
