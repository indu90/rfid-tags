<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AssignRfidTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Replace with your policy/gate/permission check as needed
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'epc' => is_string($this->input('epc'))
                ? strtoupper(trim($this->input('epc')))
                : $this->input('epc'),
            'reason' => is_string($this->input('reason'))
                ? strtolower(trim($this->input('reason')))
                : $this->input('reason'),
        ]);
    }

    public function rules(): array
    {
        return [
            'epc' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9_-]+$/', 'exists:rfid_tags,epc'],
            'reason' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'metadata.location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'metadata.device_id' => ['nullable', 'string', 'max:100'],
            'metadata.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'epc.required' => 'An RFID EPC is required to assign a tag.',
            'epc.regex' => 'EPC must contain only uppercase alphanumeric characters, hyphens, or underscores.',
            'epc.exists' => 'This RFID EPC is not registered in the system.',
        ];
    }
}
