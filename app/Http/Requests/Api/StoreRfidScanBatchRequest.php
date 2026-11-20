<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRfidScanBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Replace with your gate/policy/permission check if needed
        return true;
    }

    protected function prepareForValidation(): void
    {
        $scans = collect($this->input('scans', []))
            ->map(function ($scan) {
                if (! is_array($scan)) {
                    return $scan;
                }

                if (isset($scan['epc']) && is_string($scan['epc'])) {
                    $scan['epc'] = strtoupper(trim($scan['epc']));
                }

                return $scan;
            })
            ->values()
            ->all();

        $this->merge([
            'reader_id' => is_string($this->input('reader_id'))
                ? trim($this->input('reader_id'))
                : $this->input('reader_id'),
            'zone_id' => is_string($this->input('zone_id'))
                ? trim($this->input('zone_id'))
                : $this->input('zone_id'),
            'client_type' => is_string($this->input('client_type'))
                ? strtolower(trim($this->input('client_type')))
                : $this->input('client_type'),
            'scans' => $scans,
        ]);
    }

    public function rules(): array
    {
        return [
            'reader_id' => ['required', 'string', 'min:1', 'max:100'],
            'zone_id' => ['nullable', 'string', 'max:100'],
            'client_type' => ['nullable', 'string', 'max:50'],
            'sent_at' => ['nullable', 'date'],
            'scans' => ['required', 'array', 'min:1', 'max:500'],
            'scans.*.epc' => ['required', 'string', 'min:1', 'max:64', 'regex:/^[A-Z0-9_-]+$/'],
            'scans.*.rssi' => ['nullable', 'numeric', 'between:-120,0'],
            'scans.*.read_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'scans.*.scanned_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'reader_id.required' => 'A reader_id is required to identify the scanning source.',
            'scans.required' => 'At least one RFID scan entry is required.',
            'scans.max' => 'A single scan batch cannot contain more than 500 scans.',
            'scans.*.epc.regex' => 'EPC must contain only uppercase alphanumeric characters, hyphens, or underscores.',
            'scans.*.rssi.between' => 'RSSI must be between -120 and 0 dBm.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $validEpcs = collect($this->input('scans', []))
                ->pluck('epc')
                ->filter(fn($epc) => is_string($epc) && $epc !== '');

            if ($validEpcs->isEmpty()) {
                $validator->errors()->add('scans', 'No valid EPC values were found in the scans array.');
            }
        });
    }
}
