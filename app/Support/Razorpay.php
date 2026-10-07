<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

/**
 * Minimal Razorpay client: create an order, then verify the checkout signature.
 * Docs: https://razorpay.com/docs/payments/server-integration/
 */
class Razorpay
{
    public static function enabled(): bool
    {
        return filled(config('shop.razorpay.key')) && filled(config('shop.razorpay.secret'));
    }

    /**
     * Create a Razorpay order for our order and return its id.
     */
    public function createOrder(Order $order): string
    {
        $response = Http::withBasicAuth(config('shop.razorpay.key'), config('shop.razorpay.secret'))
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($order->total * 100), // paise
                'currency' => 'INR',
                'receipt' => $order->number,
            ])
            ->throw();

        return $response->json('id');
    }

    public function verifySignature(string $razorpayOrderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$paymentId, config('shop.razorpay.secret'));

        return hash_equals($expected, $signature);
    }

    /**
     * Webhooks are signed with the webhook secret set in the Razorpay dashboard (not the API secret).
     */
    public function verifyWebhook(string $payload, ?string $signature): bool
    {
        $secret = config('shop.razorpay.webhook_secret');

        if (blank($secret) || blank($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
