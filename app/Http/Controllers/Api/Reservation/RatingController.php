<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Services\RatingService;
// use App\Http\Requests\StoreRatingRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\RatingResource;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use Carbon\Carbon;


class RatingController extends Controller
{

    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Display a listing of the resource. 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $ratings = Rating::select('user_id', 'rating', 'comment')->get(); // تحميل العلاقات المطلوبة
        return $this->success(RatingResource::collection($ratings));
    }



    /**
     * Store a newly created resource in storage.
     * @param StoreRatingRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRatingRequest $request)
    {
        $reservationId = $request->query('reservation_id');
        $userId = $request->query('user_id');

        $validationdata = $request->validated();
        $response = $this->ratingService->create_rating($validationdata, $reservationId, $userId);
        if (!$response) {
            return $this->error();
        } else {
            return $this->success($response, 'rating created successfully', 201);
        }
    }
    // public function show(){
    //     dd(333);
    // }

    /** 
     * Display the specified rating.
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function show_rating(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        $userId = $request->query('user_id');
    
        $rating = $this->ratingService->getRatingByReservationAndUser($reservationId, $userId);
    
        if (!$rating) {
            return $this->error();
        }
    
        return $this->success($rating);
    }
    


    /** 
     * Update the specified resource in storage.
     * @param UpdateRatingRequest $request
     * @param Rating $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRatingRequest $request, Rating $rating)
    {
        $validatedRequest = $request->validated();

        $resault = $this->ratingService->update_rating($rating, $validatedRequest);

        if (!$resault) {
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
    public function destroy(Rating $rating)
    {
        $rating->delete();
        return $this->success();
    }


    /**
     * Get deleted ratings.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletedRatings()
    {
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
        $restored = $this->ratingService->restore_rating($ratingId);

        if ($restored) {
            return $this->success('Rating restored successfully.');
        } else {
            return $this->error('Failed to restore rating.', 500);
        }
    }


    /**
     * Permanently delete a rating.
     * @param int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteRating($ratingId)
    {
        $deleted = $this->ratingService->force_delete_rating($ratingId);

        if ($deleted) {
            return $this->success('Rating permanently deleted.');
        } else {
            return $this->error('Failed to permanently delete rating.', 500);
        }
    }
}
