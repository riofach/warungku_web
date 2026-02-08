<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'delivery_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'payment_method' => [
                'required', 
                Rule::in(['qris', 'tunai']),
                function ($attribute, $value, $fail) {
                    if ($this->delivery_type === 'delivery' && $value !== 'qris') {
                        $fail('Metode pembayaran untuk pesan antar harus menggunakan QRIS.');
                    }
                    if ($this->delivery_type === 'pickup' && $value !== 'tunai') {
                        $fail('Metode pembayaran untuk ambil sendiri harus menggunakan Tunai.');
                    }
                },
            ],
            'housing_block_id' => [
                'nullable', 
                'uuid', 
                'exists:housing_blocks,id',
                Rule::requiredIf(fn () => $this->delivery_type === 'delivery'),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'housing_block_id.required' => 'Lokasi (Blok) wajib dipilih untuk pesan antar.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'delivery_type.required' => 'Metode pengambilan wajib dipilih.',
        ];
    }
}
