<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Payments\MockPaymentService;

class MockPaymentController extends Controller
{
    public function store(Order $order, MockPaymentService $service)
    {
        // $this->authorize('pay', $order);

        $amountToPay = max(0, (float)$order->total_price - (float)$order->totalPaid());
        abort_if($amountToPay <= 0, 400, 'Order is already fully paid.');

        $payment = $service->pay($order, $amountToPay, [
            'initiator' => auth()->id
        ]);

        return response()->json([
            'message' => 'Payment processed successfully. (mock)',
            'payment' => $payment->only(['id', 'amount', 'status', 'method', 'transaction_id', 'processed_at']),
            'order' => [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
                'total_paid' => $order->totalPaid(),
            ]
        ], 201);
    }
}
