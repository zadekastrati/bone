<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ['file', 'image', 'max:5120'],
            'variants' => ['required', 'array', 'min:1', 'max:200'],
            'variants.*.color' => ['required', 'string', 'max:64'],
            'variants.*.size' => ['required', 'string', 'max:32'],
            'variants.*.color_hex' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            return;
        }

        if ($this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->string('name')->toString()),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $variants = $this->input('variants', []);
            $pairs = collect($variants)->map(function (array $row): string {
                return mb_strtolower(trim($row['color'])).'|'.mb_strtolower(trim($row['size']));
            });

            if ($pairs->count() !== $pairs->unique()->count()) {
                $validator->errors()->add('variants', 'Each color and size combination must be unique.');
            }

            $skus = collect($variants)->pluck('sku')->filter(fn (?string $s) => $s !== null && $s !== '');
            if ($skus->count() !== $skus->unique()->count()) {
                $validator->errors()->add('variants', 'Duplicate SKU values are not allowed.');
            }

            foreach ($variants as $i => $row) {
                $sku = $row['sku'] ?? null;
                if ($sku === null || $sku === '') {
                    continue;
                }
                if (\App\Models\ProductVariant::query()->where('sku', $sku)->exists()) {
                    $validator->errors()->add("variants.$i.sku", 'This SKU is already in use.');
                }
            }
        });
    }
}
