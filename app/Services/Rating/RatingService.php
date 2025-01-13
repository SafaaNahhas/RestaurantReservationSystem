<?php

namespace App\Services\Rating;

use Exception;
use App\Models\Rating;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RatingService
{
    /**
     * Retrieve ratings with optional filters and pagination
     *
     * @param array $data
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRating($data)
    {
        try {
            // Read the number of items per page from the data, with a default value of 10
            $perPage = $data['perPage'] ?? 10;
            $ratingValue = $data['ratingValue'] ?? null;

            // Create a cache key based on the number of items per page and the rating value
            $cacheKey = "ratings.index.per_page_{$perPage}.rating_" . ($ratingValue ?? 'all');

            // Attempt to retrieve data from the cache or store it if not available
            $ratings = Cache::remember($cacheKey, 60, function () use ($ratingValue, $perPage) {
                return Rating::query()
                    // Apply the filterByRating scope if a rating value is provided
                    ->when($ratingValue, fn ($query) => $query->filterByRating($ratingValue))
                    // Apply pagination based on the specified per-page value
                    ->paginate($perPage);
            });

            return $ratings;
        } catch (Exception $e) {
            // Log the error and throw an exception if there is a failure in fetching rating data
            Log::error('Cannot get rating data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot get rating data');
        }
    }

    //************************************************************************** */

    /**
     * Create a new rating
     *
     * @param array $data
     * @param int $reservationId
     * @param int $userId
     * @return bool
     */
    public function create_rating(array $data, $reservationId, $userId)
    {
        try {
            // Create a new rating using the provided data
            Rating::create([
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);

            // Clear the cache for the ratings index
            Cache::forget('ratings.index.rating_all');

            return true;
        } catch (\Exception $e) {
            Log::error('Cannot create rating data ' . $e->getMessage());
            throw new HttpException(500, 'Cannot create rating data');
        }
    }

    //************************************************************************** */

    /**
     * Show a specific rating
     *
     * @param Rating $rating
     * @return Rating
     */
    public function showRating(Rating $rating)
    {
        try {
            return $rating;
        } catch (\Exception $e) {
            Log::error('Cannot show rating data ' . $e->getMessage());
            throw new HttpException(500, 'Cannot show rating data');
        }
    }

    //************************************************************************** */

    /**
     * Update an existing rating
     *
     * @param Rating $rating
     * @param array $data
     * @return bool
     */
    public function update_rating(Rating $rating, array $data)
    {
        try {
            // Update the rating with the provided data
            $rating->update(array_filter([
                'rating' => $data['rating'] ?? $rating->rating,
                'comment' => $data['comment'] ?? $rating->comment,
            ]));

            // Clear the cache for the ratings index
            Cache::forget('ratings.index.rating_all');

            return true;
        } catch (\Exception $e) {
            Log::error('Cannot update rating data ' . $e->getMessage());
            throw new HttpException(500, 'Cannot update rating data');
        }
    }

    //************************************************************************** */

    /**
     * Force delete a rating
     *
     * @param Rating $rating
     * @return bool
     */
    public function forcedeleterating(Rating $rating)
    {
        try {
            // Soft delete the rating
            $rating->delete();

            // Clear the cache for the ratings index
            Cache::forget('ratings.index.rating_all');

            return true;
        } catch (\Exception $e) {
            Log::error('Cannot forcedelete rating data ' . $e->getMessage());
            throw new HttpException(500, 'Cannot forcedelete rating data');
        }
    }

    //************************************************************************** */

    /**
     * Get all soft-deleted ratings
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get_deleted_ratings()
    {
        try {
            // Retrieve all soft-deleted ratings
            $deletedRatings = Rating::onlyTrashed()->get();
            return $deletedRatings;
        } catch (\Exception $e) {
            Log::error('Error in RatingService@get_deleted_ratings: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot retrieve deleted rating data');
        }
    }

    //************************************************************************** */

    /**
     * Restore a soft-deleted rating
     *
     * @param int $ratingId
     * @return bool
     */
    public function restore_rating($ratingId)
    {
        try {
            // Find the soft-deleted rating
            $rating = Rating::onlyTrashed()->findOrFail($ratingId);

            // Restore the rating
            $rating->restore();

            // Clear the cache for the ratings index
            Cache::forget('ratings.index.rating_all');

            return true;
        } catch (\Exception $e) {
            Log::error('Cannot restore deleted rating data ' . $e->getMessage());
            throw new HttpException(500, 'Cannot restore deleted rating data');
        }
    }

    //************************************************************************** */

    /**
     * Permanently delete a soft-deleted rating
     *
     * @param int $ratingId
     * @return bool
     */
    public function permanentlyDeleteRating($ratingId)
    {
        try {
            // Find the soft-deleted rating
            $rating = Rating::onlyTrashed()->findOrFail($ratingId);

            // Permanently delete the rating
            $rating->forceDelete();

            // Clear the cache for the ratings index
            Cache::forget('ratings.index.rating_all');

            return true;
        } catch (\Exception $e) {
            Log::error('Cannot permanently delete rating data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot permanently delete rating data');
        }
    }
}
