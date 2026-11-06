<?php

namespace App\Http\Requests\Platform;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class RejectOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Organization|null $organization */
        $organization = $this->route('organization');

        return $organization !== null
            && ($this->user()?->can('reject', $organization) ?? false);
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
