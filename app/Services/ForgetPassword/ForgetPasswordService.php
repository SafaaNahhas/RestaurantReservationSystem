<?php

namespace App\Services\ForgetPassword;

use App\Mail\SendForgetPasswordCodeMail;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgetPasswordService
{
    /**
     * check if email  exists and send the code
     * @param string  $email
     * @return array status + message
     */
    public function checkEmail($email)
    {
        try {
            if (Cache::has($email)) {
                return  [
                    'status' => 400,
                    'message' => "You can't resend the code again, please try after an hour.",
                ];
            }
            $code = Cache::remember($email, 3600, function () {
                return  random_int(100000, 999999);
            });
            Mail::to($email)->send(new SendForgetPasswordCodeMail($code));
            return  [
                'status' => 200,
                'message' => "The code has been sent to your email",
            ];
        } catch (Exception $e) {
            Log::error("error in check email and send code" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * check if the code is correct
     * @param string  $email
     * @param string  $code
     *  @return array status + message
     */
    public function checkCode($email, $code)
    {
        try {
            if (Cache::has($email)) {
                $cached_code = Cache::get($email);
                if ($code != $cached_code) {
                    return  [
                        'status' => 400,
                        'message' => "The code you entered is incorrect",
                    ];
                }
                return  [
                    'status' => 200,
                    'message' => "The code you entered is correct",
                ];
            } else {
                return  [
                    'status' => 400,
                    'message' => "The code sent to this account has expired",
                ];
            }
        } catch (Exception $e) {
            Log::error("error in check code" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * change the password
     * @param string  $email
     * @param string  $password
     */
    public function changePassword($email, $password)
    {
        try {
            $user = User::where('email', '=', $email)->first();
            if ($user) {
                $user->password = Hash::make($password);
                $user->save();
                Cache::delete($email);
            } else {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            Log::error("error in change password" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in change password" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
}
