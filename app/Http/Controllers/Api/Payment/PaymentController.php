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

        //  Calling the service for payment processing
        $charge = $this->paymentService->processPayment($request->validated());

        if (!$charge) {
            return $this->error();
        } else {
            return $this->success( ' The payment was made successfully', 200);
        }
    }
}
