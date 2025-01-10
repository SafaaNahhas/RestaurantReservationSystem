<?php

namespace App\Services\Payment;

use Stripe\Charge;
use Stripe\Stripe;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class PaymentService

{

    /**
     * @param
     * @return
     */
    public function processPayment(array $data)
    {
        try {
                //Set stripe secret key
                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                $reservation = Reservation::find($data['reservation_id']);
                   // Update the reservation with the payment value  
                if ($reservation) {
                    $reservation->payment_value = $data['amount']; 
                    $reservation->save(); }
                
                // Payment processing
                return Charge::create([
                    'amount' => $data['amount'] * 100, // Convert dollars to cents
                    'currency' => 'usd', // Currency
                    'source' => $data['stripeToken'], // Tokens sent by the client
                    'description' => 'API Payment Example',
                ]);
    
            } catch (\Exception $e) {
                Log::error('Error in paymentService@processPayment: ' . $e->getMessage());
                return ['status' => 'error', 'message' => 'An unexpected error occurred'];
        }
    }
    
}