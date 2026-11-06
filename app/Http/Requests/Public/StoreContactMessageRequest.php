<?php

namespace App\Http\Requests\Public;

use App\Models\Property;
use App\Services\Property\PublicPropertyCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            $id = $this->input('property_id');
            if (empty($id)) {
                return;
            }

            $property = Property::query()->find($id);
            $catalog = app(PublicPropertyCatalog::class);

            if (! $property || ! $catalog->isPubliclyListed($property)) {
                $v->errors()->add('property_id', 'That listing is not available for inquiries.');
            }
        });
    }
}
