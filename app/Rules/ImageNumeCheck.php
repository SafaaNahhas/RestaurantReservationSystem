<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageNumeCheck implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the original name of the uploaded file
        $originalName = $value->getClientOriginalName();

        // Check for Path Traversal Attack
        // This regex checks for multiple dots in the filename (e.g., "file..name.jpg")
        if (preg_match('/\.[^.]+\./', $originalName) ||
            strpos($originalName, '..') !== false || // Check for double dots (e.g., "file/../name.jpg")
            strpos($originalName, '/') !== false || // Check for forward slashes
            strpos($originalName, '\\') !== false) { // Check for backslashes
            // If any of the checks fail, call the fail closure with an error message
            $fail('The file name is invalid due to potential path traversal attack.');
            return; // Exit the method after calling the fail closure
        }
    }
}
