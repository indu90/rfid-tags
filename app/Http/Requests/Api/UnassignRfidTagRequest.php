<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnassignRfidTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Replace with your policy/gate/permission check as needed
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => is_string($this->input('status'))
                ? strtolower(trim($this->input('status')))
                : $this->input('status'),
            'reason' => is_string($this->input('reason'))
                ? strtolower(trim($this->input('reason')))
                : $this->input('reason'),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['unassigned', 'damaged', 'lost'])],
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
            'status.in' => 'Post-unassignment status must be one of: unassigned, damaged, or lost.',
        ];
    }
}
