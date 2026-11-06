<?php

namespace App\Http\Requests\Organization;

use App\Enums\PropertyStatus;
use App\Support\Property\PropertyListingSettings;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property && $this->user()->can('update', $property);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'status' => ['required', 'in:'.implode(',', array_column(PropertyStatus::cases(), 'value'))],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ['image', 'max:5120'],
            ...PropertyListingSettings::validationRules(),
        ];
    }
}
