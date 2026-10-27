<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Fields mirror BPO Summit's "Enquire to Exhibit" form (docs/reference-audit.md B7). */
class StoreExhibitEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:160'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'industry' => ['required', 'string', 'max:120'],
            'head_office' => ['required', 'string', 'max:160'],
            'full_name' => ['required', 'string', 'max:120'],
            'designation' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'utm' => ['nullable', 'array:utm_source,utm_medium,utm_campaign,utm_term,utm_content'],
            'utm.*' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'company name',
            'website' => 'website URL',
            'industry' => 'industry / sector',
            'head_office' => 'head office location',
            'full_name' => 'full name',
            'phone' => 'phone / WhatsApp',
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Enter a phone or WhatsApp number using digits, spaces, + or -.'];
    }
}
