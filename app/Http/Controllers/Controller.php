<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
     /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($message),
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message = 'Operation failed', $status = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => trans($message),
            'data' => $data,
        ], $status);
    }

    /**
     * Return a paginated JSON response.
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginated(LengthAwarePaginator $paginator, $message = 'Operation successful', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($message),
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], $status);
    }
}
