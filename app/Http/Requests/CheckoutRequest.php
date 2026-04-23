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
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'min:10', 'max:15', 'regex:/^\d{10,15}$/'],
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
            'block_number' => [
                Rule::requiredIf(fn () => $this->delivery_type === 'delivery'),
                'nullable',
                'string',
                'regex:/^\d+$/',
                'max:2',
            ],
            'house_number' => [
                Rule::requiredIf(fn () => $this->delivery_type === 'delivery'),
                'nullable',
                'string',
                'regex:/^\d+$/',
                'max:2',
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
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.min' => 'Nomor WhatsApp minimal 10 angka.',
            'whatsapp_number.max' => 'Nomor WhatsApp maksimal 15 angka.',
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka (10-15 digit).',
            'block_number.required' => 'Nomor blok wajib diisi untuk pesan antar.',
            'block_number.regex' => 'Nomor blok hanya boleh berisi angka.',
            'house_number.required' => 'Nomor rumah wajib diisi untuk pesan antar.',
            'house_number.regex' => 'Nomor rumah hanya boleh berisi angka.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'delivery_type.required' => 'Metode pengambilan wajib dipilih.',
        ];
    }
}
