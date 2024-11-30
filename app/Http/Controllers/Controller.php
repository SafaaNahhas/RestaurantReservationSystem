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
     * Return a successful JSON response.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The success message.
     * @param int $status The HTTP status code.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => "$message Successfully",
            'data' => $data,
        ], $status);
    }


    /**
  * Return an error JSON response.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The error message.
     * @param int $status The HTTP status code.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($data = null, $message = '', $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => "$message  failed!",
            'data' => $data,
        ], $status);
    }

    /**
     * Generates a JSON response with paginated data.
     *
     * Transforms the paginated items using the provided resource class and
     * returns the transformed data along with pagination information.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator The paginator instance containing the items.
     * @param string $resourceClass The resource class used to transform the paginated items.
     * @param string $message Optional message to be included in the response.
     * @param int $status HTTP status code.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the transformed items and pagination details.
     */
    public static function paginated(LengthAwarePaginator $paginator, $resourceClass, $message = '', $status)
    {
        $transformedItems = $resourceClass::collection($paginator->items());

        return response()->json([
            'status' => 'success',
            'message' =>  $message,
            'data' => $transformedItems,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], $status);
    }

}





