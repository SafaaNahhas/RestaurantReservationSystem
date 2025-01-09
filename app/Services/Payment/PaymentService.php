<?php

namespace App\Services\Payment;

use Stripe\Charge;
use Stripe\Stripe;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processPayment(array $data)
    {
        try {
            // Set stripe secret key
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    
            // Payment processing
            $charge = Charge::create([
                'amount' => $data['amount'] * 100, // Convert dollars to cents
                'currency' => 'usd', // Currency
                'source' => $data['stripeToken'], // Tokens sent by the client
                'description' => 'Payment for reservation ID: ' . $data['reservation_id'],
            ]);
    
            // هنا يمكنك تحديث حالة الحجز في قاعدة البيانات
            // Reservation::find($data['reservation_id'])->update(['status' => 'paid']);
    
            return $charge;
        } catch (\Exception $e) {
            Log::error('Error in paymentService@processPayment: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'An unexpected error occurred'];
        }
    }
    
}
