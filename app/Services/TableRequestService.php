<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class TableRequestService
{
    /**
     *  get array of  TableRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'table_number',
            'location',
            'seat_count',
            'department_id',
        ];
    }
    /**
     *  
     * @param $validator
     *
     * throw a exception
     */
    public function failedValidation($validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 'error',
                'message' => "Validation failed Please make sure that the values entered are correct",
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}
