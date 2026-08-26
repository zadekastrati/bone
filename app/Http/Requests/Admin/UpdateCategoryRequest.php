<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'attach_image' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator): void {
            $path = $this->input('attach_image');

            if (! is_string($path) || $path === '') {
                return;
            }

            /** @var Category $category */
            $category = $this->route('category');

            if (Category::query()->where('image_path', $path)->where('id', '!=', $category->id)->exists()) {
                $validator->errors()->add('attach_image', 'This file is already used as another category\'s image. Refresh the library and pick again.');
            }

            if (! Storage::disk('public')->exists($path)) {
                $validator->errors()->add('attach_image', 'This file was not found in storage.');
            }
        });
    }
}
