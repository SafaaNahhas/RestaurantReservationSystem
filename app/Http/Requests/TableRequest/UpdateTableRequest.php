<?php

namespace App\Http\Requests\TableRequest;

use Illuminate\Support\Facades\Log;

class UpdateTableRequest extends BaseTableRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * Merges common table rules with update-specific rules:
     * - All fields are optional (sometimes)
     * - table_number must be unique except for current table
     * - Maintains existing values if fields are not provided
     *
     * @return array<string, array> Array of validation rules
     */
    public function rules(): array
    {
        $commonRules = $this->tableRequestService->getCommonRules();
        $tableId = $this->route('table');

        return [
            'table_number' => array_merge(
                $commonRules['table_number'],
                ['sometimes', "unique:tables,table_number,{$tableId}"]  // Unique except current
            ),
            'location' => array_merge(
                $commonRules['location'],
                ['sometimes']
            ),
            'seat_count' => array_merge(
                $commonRules['seat_count'],
                ['sometimes']
            ),
        ];
    }

    /**
     * Handle successful validation for table update.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs the update
     * attempt for monitoring and auditing purposes.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Update Table form validation passed', [
            'table_id' => $this->route('table'),                // Target table identifier
            'table_number' => $this->table_number,                     // Updated table number
            'location' => $this->location,                             // Updated location
            'seat_count' => $this->seat_count,                         // Updated seat count
            'ip' => $this->ip(),                                       // Client IP for audit trail
            'user_agent' => $this->userAgent(),                        // Browser/device information
            'updated_fields' => array_keys($this->only(                // Fields being modified
                ['table_number', 'location', 'seat_count']
            )),
            'updated_by' => auth()->id(),                              // User who made the update
        ]);
    }
}