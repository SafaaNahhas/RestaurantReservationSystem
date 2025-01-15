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


    /**
     * Process a payment.
     *
     * @param ProcessPaymentRequest $request The request containing payment details.
     * @return IlluminateHttpJsonResponse The response indicating the status of the payment.
     */

     public function processPayment(ProcessPaymentRequest $request)
     {
         $response = $this->paymentService->processPayment($request->validated());

         if ($response['status'] === 'error') {
             return response()->json(['status' => 'error', 'message' => $response['message']], 500);
         }

         $chargeId = isset($response['charge']) ? $response['charge']->id : null;

         return response()->json([
             'status' => 'success',
             'message' => 'The payment was made successfully',
             'charge_id' => $chargeId,
         ], 200);
     }

    /**
     *  Adding an amount to a previous payment
     *
     * @param ProcessPaymentRequest $request The request containing payment details.
     * @return IlluminateHttpJsonResponse The response indicating the status of the payment.
     */

    public function addprocessPayment(ProcessPaymentRequest $request)
    {
        $response = $this->paymentService->addprocessPayment($request->validated());

        if ($response['status'] === 'error') {
            return response()->json(['status' => 'error', 'message' => $response['message']], 500);
        } else {
            return response()->json(['status' => 'success', 'message' => 'The payment was made successfully', 'charge_id' => $response['charge']->id], 200);
        }
    }
}
