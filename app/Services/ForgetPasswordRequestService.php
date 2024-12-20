<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class ForgetPasswordRequestService
{
    /**
     *  get array of  ForgetPasswordRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'password',
            'email',
            'code',
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