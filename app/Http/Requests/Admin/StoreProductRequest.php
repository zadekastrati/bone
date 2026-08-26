<?php

namespace App\Http\Requests\Admin;

use App\Enums\TrainingTag;
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
            'style_code' => ['nullable', 'string', 'max:64', Rule::unique('products', 'style_code')],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'training_tags' => ['nullable', 'array'],
            'training_tags.*' => [Rule::in(TrainingTag::values())],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ['file', 'mimes:jpeg,jpg,png,webp,mp4,webm,mov,ogg,m4v', 'max:102400'],
            'variant_images' => ['nullable', 'array'],
            'variant_images.*' => ['array', 'max:12'],
            'variant_images.*.*' => ['file', 'mimes:jpeg,jpg,png,webp,mp4,webm,mov,ogg,m4v', 'max:102400'],
            'attach_images' => ['nullable', 'array', 'max:50'],
            'attach_images.*' => ['string', 'max:2048'],
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

            $this->validateAttachPaths($validator);
        });
    }

    /**
     * Guards against attaching the same R2 library file twice: once within this
     * submission, and once against files already attached to any product
     * (a stale picker list from before someone else claimed the file). Also
     * rejects paths that don't exist on disk. Mirrors UpdateProductRequest's
     * version of this check.
     */
    private function validateAttachPaths(Validator $validator): void
    {
        $paths = collect($this->input('attach_images', []))
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values();

        if ($paths->isEmpty()) {
            return;
        }

        if ($paths->count() !== $paths->unique()->count()) {
            $validator->errors()->add('attach_images', 'The same library file was selected more than once — remove the duplicate and try again.');
        }

        $alreadyAttached = \App\Models\ProductImage::query()->whereIn('path', $paths->all())->pluck('path');
        if ($alreadyAttached->isNotEmpty()) {
            $validator->errors()->add('attach_images', 'These files are already attached to a product: '.$alreadyAttached->implode(', ').'. Refresh the library and pick again.');
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $missing = $paths->reject(fn (string $path): bool => $disk->exists($path));
        if ($missing->isNotEmpty()) {
            $validator->errors()->add('attach_images', 'These files were not found in storage: '.$missing->implode(', '));
        }
    }
}
