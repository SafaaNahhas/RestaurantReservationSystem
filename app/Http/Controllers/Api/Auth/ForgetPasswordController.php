<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPassword\CheckUserCodeRequest;
use App\Http\Requests\ForgetPassword\CheckUserEmailRequest;
use App\Http\Requests\ForgetPassword\CheckUserPasswordRequest;
use App\Services\ForgetPasswordService;

class ForgetPasswordController extends Controller
{
    protected $forgetPasswordService;
    public function __construct(ForgetPasswordService $forgetPasswordService)
    {
        $this->forgetPasswordService = $forgetPasswordService;
    }
    /**
     * check email
     * @param CheckUserEmailRequest request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(CheckUserEmailRequest $request)
    {
        $email = $request->validated()['email'];
        $data = $this->forgetPasswordService->checkEmail($email);
        if ($data['status'] == 200) {
            return $this->success(message: $data['message'], status: $data['status']);
        } else {
            return $this->error(message: $data['message'], status: $data['status']);
        }
    }
        /**
     * check code
     * @param CheckUserCodeRequest request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCode(CheckUserCodeRequest $request)
    {
        $email = $request->validated()['email'];
        $code = $request->validated()['code'];
        $data = $this->forgetPasswordService->checkCode($email, $code);
        if ($data['status'] == 200) {
            return $this->success(message: $data['message'], status: $data['status']);
        } else {
            return $this->error(message: $data['message'], status: $data['status']);
        }
    }

    /**
     * change password
     * @param CheckUserPasswordRequest request
     * @return \Illuminate\Http\JsonResponse
     */

    public function changePassword(CheckUserPasswordRequest $request)
    {
        $email = $request->validated()['email'];
        $password = $request->validated()['password'];

        $this->forgetPasswordService->changePassword($email, $password);

        return $this->success(message: "password changed", status: 200);
    }
}