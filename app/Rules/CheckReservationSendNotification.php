<?php

namespace App\Rules;

use App\Enums\ReservationSendNotificationOptions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckReservationSendNotification implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $arrayOfOpations = array_column(ReservationSendNotificationOptions::cases(), 'value');

        $opations = implode(", ", array_column(ReservationSendNotificationOptions::cases(), 'value'));
        $arrayOfOpationsEntred=$value;
        foreach( $arrayOfOpationsEntred as $op)
        if (!(in_array($op, $arrayOfOpations))) {
            $fail('The Reservation Send Notification value is invalid , pleace enter:' .$opations);
        } 
      }
}