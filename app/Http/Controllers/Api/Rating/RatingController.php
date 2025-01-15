<?php

namespace App\Http\Controllers\Api\Rating;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\Rating\RatingService;
use App\Http\Resources\Rating\RatingResource;
use App\Http\Requests\Rating\StoreRatingRequest;
use App\Http\Requests\Rating\UpdateRatingRequest;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RatingController extends Controller
{

    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing query parameters.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = [
            'perPage' => $request->input('per_page', 10),
            'ratingValue' => $request->input('rating')
        ];
        $ratings = $this->ratingService->getRating($data);
        return $this->paginated($ratings, RatingResource::class, 'Ratings fetched successfully', 200);
    }
    //************************************************************************************************** */


    /**
     * Store a newly created resource in storage.
     * @param StoreRatingRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRatingRequest $request)
    {

        $validationdata = $request->validated();
        $reservationId = $request->input('reservation_id');
        $userId = $request->query('user_id');
        if ($request->user()->cannot('create', [Rating::class, $userId, $reservationId])) {
            throw new UnauthorizedException(403, "You can only rate your own reservations.");
        }
        $response = $this->ratingService->create_rating($validationdata, $reservationId, $userId);
        return $this->success($response, 'Rating created successfully', 201);

    }

    //**************************************************************** */

    /**
     * Display the specified rating.
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Rating $rating)
    {
        $this->authorize('show', $rating);
        $rating= $this->ratingService->showRating($rating);
        return self::success(new RatingResource($rating), 'rating  data ', 200);

    }
    //************************************************************** */

    /**
     * Update the specified resource in storage.
     * @param UpdateRatingRequest $request
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRatingRequest $request, Rating $rating)
    {
        if ($request->user()->cannot('update', $rating)) {
            throw new UnauthorizedException(403, "You can only update your own ratings.");
        }
        $validatedRequest = $request->validated();
        $result = $this->ratingService->update_rating($rating, $validatedRequest);

        return $this->success($result, 'Rating updated successfully', 200);
    }

    //************************************************************************** */


    /**
     * Remove the specified resource from storage.
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */


    public function destroy(Request $request, Rating $rating)
    {
        if ($request->user()->cannot('delete', $rating)) {
            throw new UnauthorizedException(403, "You can only delete your own ratings.");
        }
        $this->ratingService->forcedeleterating($rating);
        return $this->success(null, 'Rating deleted successfully', 200);

    }

    //********************************************************************* */

    /**
     * Get deleted ratings.
     * @return \Illuminate\Http\JsonResponse
     */


    public function getDeletedRatings()
    {

        $this->authorize('get_deleting', Rating::class);
        $deletedRatings = $this->ratingService->get_deleted_ratings();
        return $this->success($deletedRatings, 'Deleted ratings retrieved successfully.');

    }



    //******************************************************************************* */
    /**
     * Restore a deleted rating.
     * @param int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreRating($ratingId)
    {
        $this->authorize('restore', Rating::class);

        $restored = $this->ratingService->restore_rating($ratingId);

        return $this->success($restored, 'Rating restored successfully.');
    }


    //******************************************************** */

    /**
     * Permanently delete a rating.
     * @param int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteRating(Request $request, $ratingId)
    {
        $this->authorize('forceDelete', Rating::class);

        $deleted = $this->ratingService->permanentlyDeleteRating($ratingId);

        return $this->success(null,'Rating permanently deleted.');

    }
}
