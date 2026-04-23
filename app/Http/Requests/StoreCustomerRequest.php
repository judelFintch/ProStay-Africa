<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_code' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:20'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:20'],
            'identity_document' => ['nullable', 'string', 'max:100'],
            'identity_document_type' => ['nullable', 'string', 'max:50'],
            'identity_document_issue_place' => ['nullable', 'string', 'max:255'],
            'identity_document_issued_at' => ['nullable', 'date'],
            'identity_document_expires_at' => ['nullable', 'date', 'after_or_equal:identity_document_issued_at'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'guest_preferences' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'marketing_source' => ['nullable', 'string', 'max:255'],
            'vip_status' => ['sometimes', 'boolean'],
            'blacklisted' => ['sometimes', 'boolean'],
            'is_identified' => ['sometimes', 'boolean'],
        ];
    }
}
