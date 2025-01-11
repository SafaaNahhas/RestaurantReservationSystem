<?php

namespace App\Rules;

use App\Enums\SendNotificationOptions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckSendNotificationOptions implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $arrayOfOpations = array_column(SendNotificationOptions::cases(), 'value');

        $opations = implode(", ", array_column(SendNotificationOptions::cases(), 'value'));
        $arrayOfOpationsEntred = $value;
        foreach ($arrayOfOpationsEntred as $op)
            if (!(in_array($op, $arrayOfOpations))) {
                $fail('The Send Notification Options value is invalid , pleace enter:' . $opations);
            }
    }
}
