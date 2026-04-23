<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CheckoutRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CheckoutRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes_for_valid_delivery_order()
    {
        $data = [
            'customer_name' => 'John Doe',
            'block_number' => '14',
            'house_number' => '08',
            'delivery_type' => 'delivery',
            'payment_method' => 'qris',
        ];

        $request = new CheckoutRequest();
        $request->merge($data);
        
        // Bind the request instance so 'rules()' can access input via $this
        $validator = Validator::make($data, $request->rules());

        if ($validator->fails()) {
             dump($validator->errors()->toArray());
        }

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_for_valid_pickup_order()
    {
        $data = [
            'customer_name' => 'John Doe',
            'delivery_type' => 'pickup',
            'payment_method' => 'tunai',
        ];

        $request = new CheckoutRequest();
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_if_customer_name_missing()
    {
        $data = [
            // 'customer_name' => 'John Doe',
            'delivery_type' => 'pickup',
            'payment_method' => 'tunai',
        ];

        $request = new CheckoutRequest();
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('customer_name', $validator->errors()->toArray());
    }

    public function test_validation_fails_if_delivery_requires_block_number()
    {
        $data = [
            'customer_name' => 'John Doe',
            'delivery_type' => 'delivery',
            'payment_method' => 'qris',
            // 'block_number' => '14', // Missing
            // 'house_number' => '08', // Missing
        ];

        $request = new CheckoutRequest();
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('block_number', $validator->errors()->toArray());
    }

    public function test_validation_fails_if_delivery_uses_cash()
    {
        $data = [
            'customer_name' => 'John Doe',
            'block_number' => '14',
            'house_number' => '08',
            'delivery_type' => 'delivery',
            'payment_method' => 'tunai', // Invalid
        ];

        $request = new CheckoutRequest();
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_if_pickup_uses_qris()
    {
        $data = [
            'customer_name' => 'John Doe',
            'delivery_type' => 'pickup',
            'payment_method' => 'qris', // Invalid
        ];

        $request = new CheckoutRequest();
        $request->merge($data);

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
    }
}
