<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MockPaymentService
{

    public function pay(Order $order, float $amount, array $meta = []): Payment
    {
        return DB::transaction(function () use ($order, $amount, $meta) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => PaymentMethod::MOCK,
                'status' => PaymentStatus::SUCCEEDED,
                'transaction_id' => uniqid('mock_', true),
                'payload' => [
                    'note' => 'Mock Payment for Testing',
                    'meta' => $meta
                ],
                'processed_at' => now(),
            ]);

            $order->refreshPaymentStatus();

            return $payment;
        });
    }
}
