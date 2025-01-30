<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PaymobCheckoutTest extends TestCase
{
    public function testCreateOrder()
    {
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY')
        ]);
        $token = $response->object()->token;
        $this->assertEquals(201, $response->status());
        Log::info('Token:', [$token]);

        $response2 = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
            "auth_token" => $token,
            "delivery_needed" => "false",
            "amount_cents" => "100000",
            "currency" => "EGP",
            "items" => [[
                "name" => "ASC1515",
                "amount_cents" => "500000",
                "description" => "Smart Watch",
                "quantity" => "1"
            ]],
        ]);
        Log::info('Order Response:', [$response2->body()]);
        $this->assertEquals(201, $response2->status());

        $data = [
            "auth_token" => $token,
            "amount_cents" => "100",
            "expiration" => 3600,
            "order_id" => $response2->object()->id,
            "billing_data" =>  [
                "apartment" => "803",
                "email" => "claudette09@exa.com",
                "floor" => "42",
                "first_name" => "Clifford",
                "street" => "Ethan Land",
                "building" => "8028",
                "phone_number" => "+86(8)9135210487",
                "shipping_method" => "PKG",
                "postal_code" => "01898",
                "city" => "Jaskolskiburgh",
                "country" => "CR",
                "last_name" => "Nicolas",
                "state" => "Utah"
            ],
            "currency" => "EGP",
            "integration_id" => env('PAYMOB_INTEGRATION_ID')
        ];
        $response3 = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', $data);
        Log::info('Payment Response:', [$response3->body()]);
        $this->assertEquals(201, $response2->status());
    }
}
