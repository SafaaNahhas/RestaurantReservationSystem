<?php
namespace App\Http\Controllers\Api\Auth;

use App\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\AuthRequest\LoginRequest;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\AuthRequest\RegisterRequest;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

/**
 * Class AuthController
 *
 * This controller handles the authentication-related actions such as login, registration, logout, and token refresh.
 * It interacts with the AuthService to perform these actions.
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService The service that handles authentication logic.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the login request.
     *
     * @param LoginRequest $request The validated request data.
     * @return \Illuminate\Http\JsonResponse The response containing the authentication token or an error message.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $response = $this->authService->login($credentials);

        return response()->json($response);
    }

    /**
     * Handle the registration request.
     *
     * @param RegisterRequest $request The validated request data.
     * @return \Illuminate\Http\JsonResponse The response containing the authentication token or an error message.
     */
    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register($request->all());

        return response()->json($response, $response['status']);
    }

    /**
     * Handle the logout request.
     *
     * @return \Illuminate\Http\JsonResponse The response confirming the user has logged out.
     */
    public function logout()
    {
        $response = $this->authService->logout();

        return response()->json($response, $response['status']);
    }

    /**
     * Handle the token refresh request.
     *
     * @return \Illuminate\Http\JsonResponse The response containing the new authentication token.
     */
    public function refresh()
    {
        $response = $this->authService->refresh();

        return response()->json($response);
    }
      /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        try {
            // استرجاع المستخدم الذي تم توثيقه بناءً على التوكن
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // إرجاع بيانات المستخدم مع الـ 200 OK
            return response()->json(['user' => $user], 200);
        } catch (JWTException $e) {
            // في حال حدوث أي خطأ في التوكن
            return response()->json(['error' => 'Token is invalid'], 401);
        }
    }
}
