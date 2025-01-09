<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use App\Http\Requests\Payment\ProcessPaymentRequest;

class PaymentController extends Controller
{

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processPayment(ProcessPaymentRequest $request)
    {
        // Calling the service for payment processing
        $charge = $this->paymentService->processPayment($request->validated());
    
        if (!$charge) {
            return response()->json(['status' => 'error', 'message' => 'Payment failed'], 500);
        } else {
            return response()->json(['status' => 'success', 'message' => 'The payment was made successfully', 'charge_id' => $charge->id], 200);
        }
    }
    
}
