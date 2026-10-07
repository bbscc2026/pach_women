<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Cart;
use App\Support\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    /**
     * Razorpay Checkout posts here after a successful payment.
     */
    public function verify(Request $request, Razorpay $razorpay, Cart $cart)
    {
        $data = $request->validate([
            'order' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order = Order::where('number', $data['order'])
            ->where('user_id', $request->user()->id)
            ->where('payment_method', 'razorpay')
            ->firstOrFail();

        if ($order->payment_status !== 'paid') {
            $valid = $order->razorpay_order_id === $data['razorpay_order_id']
                && $razorpay->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

            if (! $valid) {
                $order->update(['payment_status' => 'failed']);

                return redirect()->route('checkout')->with('error', 'We could not verify your payment. If money was deducted, contact us with order '.$order->number.'.');
            }

            $order->markPaid($data['razorpay_payment_id']);
        }

        $cart->clear();

        return redirect()->route('order.placed', $order);
    }

    /**
     * Server-to-server notice from Razorpay. Records payments even when the customer
     * closes the browser before returning to the shop.
     * Set up in Razorpay dashboard → Webhooks with events "payment.captured" and "order.paid".
     */
    public function webhook(Request $request, Razorpay $razorpay)
    {
        $payload = $request->getContent();

        if (! $razorpay->verifyWebhook($payload, $request->header('X-Razorpay-Signature'))) {
            Log::warning('Razorpay webhook with invalid signature');

            return response()->json(['ok' => false], 400);
        }

        $event = json_decode($payload, true) ?: [];

        if (! in_array($event['event'] ?? null, ['payment.captured', 'order.paid'], true)) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $payment = data_get($event, 'payload.payment.entity', []);
        $order = Order::where('razorpay_order_id', $payment['order_id'] ?? null)->first();

        if (! $order) {
            return response()->json(['ok' => true, 'unknown_order' => true]);
        }

        // Only accept the full amount, in rupees.
        if ((int) ($payment['amount'] ?? 0) !== (int) round($order->total * 100) || ($payment['currency'] ?? 'INR') !== 'INR') {
            Log::warning('Razorpay webhook amount mismatch', ['order' => $order->number, 'amount' => $payment['amount'] ?? null]);

            return response()->json(['ok' => false, 'amount_mismatch' => true], 422);
        }

        $order->markPaid((string) ($payment['id'] ?? ''));

        return response()->json(['ok' => true]);
    }
}
