<?php

namespace App\Http\Requests\TableRequest;

use Illuminate\Support\Facades\Log;

class StoreTableRequest extends BaseTableRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * Merges common table rules with create-specific rules:
     * - table_number: Must be unique across all tables
     * - All fields are required for creation
     *
     * @return array<string, array> Array of validation rules
     */

    public function rules(): array
    {
        $commonRules = $this->tableRequestService->getCommonRules();

        return [

            // 'table_number' => ['required', 'string', 'min:2', 'max:255', 'unique:tables,table_number'],
            // 'location' => ['required', 'string', 'min:6', 'max:255'],
            // 'seat_count' => ['required', 'integer', 'gt:0'],
            // //  'department_id' => ['required', 'exists:departments,id', 'gt:0']

            'table_number' => array_merge(
                $commonRules['table_number'],
                ['required', 'unique:tables,table_number']
            ),
            'location' => array_merge(
                $commonRules['location'],
                ['required']
            ),
            'seat_count' => array_merge(
                $commonRules['seat_count'],
                ['required']
            ),
        ];
    }

    /**
     * Handle successful validation for table creation.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It provides
     * an opportunity to perform additional operations or logging
     * before the main controller action is executed.
     *
     * The method logs successful validation attempts to help monitor
     * system usage and track form submission patterns.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with table details
        Log::info('Store Table form validation passed', [
            'table_number' => $this->table_number,                  // Table number being created
            'location' => $this->location,                          // Location of the table
            'seat_count' => $this->seat_count,                      // Number of seats
            'ip' => $this->ip(),                                    // Client IP for request tracking
            'user_agent' => $this->userAgent(),                     // Browser/device information
            'request_time' => now()->toDateTimeString(),            // Time of request
            'validated_fields' => array_keys($this->validated())    // List of validated fields
        ]);
    }
}