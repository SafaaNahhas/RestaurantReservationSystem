<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class RoleRequestService
{
    /**
     *  get array of  RoleRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'name' => 'Role Name',
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
                'message' => 'Validation failed. Please check the provided data.',
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}