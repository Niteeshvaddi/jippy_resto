<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $vendorId = optional($this->user())->vendorID;

        return [
            'title' => ['required', 'string', 'max:255'],
            'restaurant_slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('vendors', 'restaurant_slug')->ignore($vendorId, 'id'),
            ],
            'zone_slug' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'zone_id' => ['nullable', 'string', 'exists:zone,id'],
            'cuisine_id' => ['nullable', 'string', 'exists:vendor_cuisines,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['string', 'exists:vendor_categories,id'],
            'restaurant_cost' => ['nullable', 'numeric', 'min:0'],
            'open_dine_time' => ['nullable', 'date_format:H:i'],
            'close_dine_time' => ['nullable', 'date_format:H:i'],
            'admin_commission' => ['nullable', 'numeric', 'min:0'],
            'admin_commission_type' => ['required_with:admin_commission', 'in:Percent,Fixed'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:5120'],
            'is_open' => ['nullable', 'boolean'],
            'enabled_dine_in_future' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'restaurant name',
            'restaurant_slug' => 'restaurant slug',
            'zone_id' => 'zone',
            'cuisine_id' => 'cuisine',
            'category_ids' => 'categories',
        ];
    }
}

