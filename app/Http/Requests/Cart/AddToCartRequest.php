<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'color' => ['required', 'string', 'max:64'],
            'size' => ['required', 'string', 'max:32'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $product = Product::query()->find($this->input('product_id'));
            if ($product === null || ! $product->is_active || $product->trashed()) {
                $validator->errors()->add('product_id', 'This product is not available.');

                return;
            }

            $exists = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('color', $this->input('color'))
                ->where('size', $this->input('size'))
                ->exists();

            if (! $exists) {
                $validator->errors()->add('size', 'The selected color and size combination is not available.');
            }
        });
    }

    public function variant(): ProductVariant
    {
        return ProductVariant::query()
            ->where('product_id', $this->validated('product_id'))
            ->where('color', $this->validated('color'))
            ->where('size', $this->validated('size'))
            ->firstOrFail();
    }
}
