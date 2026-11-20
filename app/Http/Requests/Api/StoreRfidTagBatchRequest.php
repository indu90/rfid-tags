<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRfidTagBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Replace with your gate/policy/permission check if needed
        return true;
    }

    protected function prepareForValidation(): void
    {
        $epcs = collect($this->input('epcs', []))
            ->map(fn($epc) => is_string($epc) ? strtoupper(trim($epc)) : $epc)
            ->values()
            ->all();

        $tags = collect($this->input('tags', []))
            ->map(function ($tag) {
                if (! is_array($tag)) {
                    return $tag;
                }

                if (isset($tag['epc']) && is_string($tag['epc'])) {
                    $tag['epc'] = strtoupper(trim($tag['epc']));
                }

                if (isset($tag['status']) && is_string($tag['status'])) {
                    $tag['status'] = strtolower(trim($tag['status']));
                }

                return $tag;
            })
            ->values()
            ->all();

        $this->merge([
            'epcs' => $this->has('epcs') ? $epcs : null,
            'tags' => $this->has('tags') ? $tags : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'epcs' => ['nullable', 'array', 'min:1', 'max:1000', 'required_without:tags'],
            'epcs.*' => ['required', 'string', 'min:1', 'max:64', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],

            'tags' => ['nullable', 'array', 'min:1', 'max:1000', 'required_without:epcs'],
            'tags.*.epc' => ['required', 'string', 'min:1', 'max:64', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'tags.*.status' => ['nullable', 'string', Rule::in(['unassigned', 'damaged', 'lost', 'retired'])],
            'tags.*.metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'epcs.required_without' => 'Provide either an epcs array or a tags array.',
            'epcs.max' => 'A single tag batch cannot contain more than 1,000 EPCs.',
            'epcs.*.regex' => 'Each EPC must contain only uppercase alphanumeric characters, hyphens, or underscores.',
            'epcs.*.distinct' => 'Duplicate EPCs are not allowed within the same batch.',
            'tags.required_without' => 'Provide either a tags array or an epcs array.',
            'tags.max' => 'A single tag batch cannot contain more than 1,000 tags.',
            'tags.*.epc.regex' => 'Each tag EPC must contain only uppercase alphanumeric characters, hyphens, or underscores.',
            'tags.*.epc.distinct' => 'Duplicate tag EPCs are not allowed within the same batch.',
            'tags.*.status.in' => 'Initial tag status must be one of: unassigned, damaged, lost, or retired.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->filled('epcs') && $this->filled('tags')) {
                $validator->errors()->add(
                    'epcs',
                    'Send either epcs or tags in a batch request, not both at the same time.'
                );
            }
        });
    }
}
