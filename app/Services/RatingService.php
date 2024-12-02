<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Support\Facades\Log;

class RatingService
{


    /**
     * Fetch a rating based on reservation ID and user ID.
     *
     * @param int $reservationId
     * @param int $userId
     * @return Rating|null
     */
    public function getRatingByReservationAndUser($reservationId, $userId)
    {
        try {

            $rating=Rating::where('reservation_id', $reservationId)
                ->where('user_id', $userId)
                ->first();
            return $rating;
        } catch (\Exception $e) {
            Log::error('Error in ratingService@create_rating: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * store the rating
     * @param Rating $rating
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_rating(array $data, $reservationId, $userId)
    {

        try {
            //Create a new rating using the provided data
            Rating::create([
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error in ratingService@create_rating: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * update the rating
     * @param Rating $rating
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_rating(Rating $rating, array $data)
    {
        try {

            //Update only the fields that are provided in the data array
            $rating->update(array_filter([
                'rating' => $data['rating'] ?? $rating->rating,
                'comment' => $data['comment'] ?? $rating->comment,

            ]));

            return true;
        } catch (\Exception $e) {
            Log::error('Error in TaskService@update_Task' . $e->getMessage());
            return false;
        }
    }


    /**
     * get all deleted rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_deleted_ratings()
    {
        try {
            // Fetch only soft-deleted ratings
            $deletedRatings = Rating::onlyTrashed()->get();

            return $deletedRatings;
        } catch (\Exception $e) {
            Log::error('Error in RatingService@get_deleted_ratings: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * restore the rating
     * @param  $ratingId
     * @return \Illuminate\Http\JsonResponse
     */

    public function restore_rating($ratingId)
    {
        try {
            // Find the soft-deleted rating
            $rating = Rating::onlyTrashed()->findOrFail($ratingId);

            // Restore the rating
            $rating->restore();

            return true;
        } catch (\Exception $e) {
            Log::error('Error in RatingService@restore_rating: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * force_delete the rating
     * @param  $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function force_delete_rating($ratingId)
    {
        try {
            // Find the soft-deleted rating
            $rating = Rating::onlyTrashed()->findOrFail($ratingId);

            // Permanently delete the rating
            $rating->forceDelete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error in RatingService@force_delete_rating: ' . $e->getMessage());
            return false;
        }
    }
}
