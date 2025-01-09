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
        // Read the number of items per page from the request, with a default value of 10
        $perPage = $request->input('per_page', 10);

        $ratingValue = $request->input('rating');

        // Create a cache key based on the number of items per page and the rating value
        $cacheKey = "ratings.index.per_page_{$perPage}.rating_" . ($ratingValue ?? 'all');

        // Attempt to retrieve data from the cache or store it if not available
        $ratings = Cache::remember($cacheKey, 60, function () use ($ratingValue, $perPage) {
            return Rating::query()
                // Apply the filterByRating scope if a rating value is provided
                ->when($ratingValue, fn($query) => $query->filterByRating($ratingValue))
                // Apply pagination based on the specified per-page value
                ->paginate($perPage);
        });

        return $this->paginated($ratings, RatingResource::class, 'Ratings fetched successfully', 200);
    }





    /**
     * Store a newly created resource in storage.
     * @param StoreRatingRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRatingRequest $request)
    {

        $reservationId = $request->input('reservation_id');

        $userId = $request->query('user_id');

        if ($request->user()->cannot('create', [Rating::class, $userId, $reservationId])) {
            throw new UnauthorizedException(403, "You can only rate your own reservations.");
        }


        $validationdata = $request->validated();
        $response = $this->ratingService->create_rating($validationdata, $reservationId, $userId);
        if (!$response) {

            Cache::forget('ratings.index.rating_all');

            return $this->error();
        } else {
            return $this->success($response, 'rating created successfully', 201);
        }
    }


    /**
     * Display the specified rating.
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Rating $rating)
    {
        $this->authorize('show', $rating);

        try {

            // $rating = Rating::select('user_id', 'rating', 'comment')->first();
            return new RatingResource($rating);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error' => true,
                'message' => "You don't have permission to perform this action."
            ], 403);
        }
    }




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

        $resault = $this->ratingService->update_rating($rating, $validatedRequest);

        if (!$resault) {
            Cache::forget('ratings.index.rating_all');
            return $this->error();
        } else {
            return $this->success($resault, 'rating updated successfully', 200);
        }
    }

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
        $rating->delete();

        Cache::forget('ratings.index.rating_all');

        return $this->success(null,'Rating deleted successfully',200);
    }


    /**
     * Get deleted ratings.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletedRatings()
    {

        $this->authorize('get_deleting', Rating::class);

        $deletedRatings = $this->ratingService->get_deleted_ratings();

        if ($deletedRatings) {
            return $this->success($deletedRatings, 'Deleted ratings retrieved successfully.');
        } else {
            return $this->error('Failed to retrieve deleted ratings.', 500);
        }
    }



    /**
     * Restore a deleted rating.
     * @param int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreRating($ratingId)
    {
        $this->authorize('restore', Rating::class);

        $restored = $this->ratingService->restore_rating($ratingId);

        if ($restored) {
            return $this->success($restored, 'Rating restored successfully.');
        } else {
            return $this->error('Failed to restore rating.', 500);
        }
    }


    /**
     * Permanently delete a rating.
     * @param int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteRating(Request $request, $ratingId)
    {
        $this->authorize('forceDelete', Rating::class);

        $deleted = $this->ratingService->force_delete_rating($ratingId);
        if ($deleted) {
            return $this->success('Rating permanently deleted.');
        } else {
            return $this->error('Failed to permanently delete rating.', 500);
        }
    }
}
