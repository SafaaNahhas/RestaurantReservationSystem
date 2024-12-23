<?php

namespace App\Services;


use App\Models\Category;
use App\Models\FoodCategory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\Auth;

class FoodCategoryService{


    /**
     * List all Food Category
     */
    public function listCategory($per_page = 10)
    {
        try {
            return FoodCategory::paginate($per_page);
        } catch (Exception $e) {
         Log::error('Error in Get all FoodCategory'. $e->getMessage());
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
     * Create a new Category.
     *
     * @param array $category
     * @return \App\Models\Category
     */
    public function createCategory(array $data)
    {
        try {
            // Create a new Category record with the provided data
            return FoodCategory::create([
                'category_name'=> $data['category_name'],
                'description'=> $data['description'] ?? null,
                'user_id'=>Auth()->id(),
            ]);
        } catch (Exception $e) {
          Log::error('Error creating Category: ' . $e->getMessage());
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
     * Get the details of a specific Category by its ID.
     *
     * @param int $id
     * @return \App\Models\Category
     */
    public function getCategory(int $id)
    {
        try {
            // Find the Category by ID or fail with a 404 error if not found
            return FoodCategory::findOrFail($id);

        } catch (ModelNotFoundException $e) {
            Log::error("error in get a FoodCategory" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in get a FoodCategory" . $e->getMessage());

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
     * Update the details of a specific book.
     *
     * @param array $data
     * @param int $id
     * @return \App\Models\Book
     */
    public function updateFoodCategory(array $data, int $id)
    {
        try {
            // Find the food category by ID or fail with a 404 error if not found
            $foodCategory = FoodCategory::findOrFail($id);

            // Update the category with the provided data, filtering out null values
            $foodCategory->update(array_filter([
                'category_name'=> $data['category_name'] ?? $foodCategory->category_name,
                'description'=> $data['description'] ?? $foodCategory->description,
            ]));

            // Return the updated food category
            return $foodCategory;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in update FoodCategory" . $e->getMessage());

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
     * Delete a specific Category by its ID.
     *
     * @param int $id
     * @return void
     */
    public function deleteFoodCategory(int $id)
    {
        try {
            // Find the Category by ID or fail with a 404 error if not found
            $category = FoodCategory::findOrFail($id);

            // Delete the Category
            $category->delete();
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in delete a FoodCategory" . $e->getMessage());

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
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashedListFoodCategory($perPage)
    {
        try {
            return FoodCategory::onlyTrashed()->paginate($perPage);
        }  catch (Exception $e) {
            Log::error("error in display list of trashed FoodCategory" . $e->getMessage());

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
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be restored.
     * @return \App\Models\Task
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreFoodCategory($id)
    {
        try {
            $category = FoodCategory::onlyTrashed()->findOrFail($id);
            $category->restore();
            return $$category;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in restore a FoodCategory" . $e->getMessage());

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
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteFoodCategory($id)
    {
        try {
            $Task = FoodCategory::onlyTrashed()->findOrFail($id);

            $Task->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error  in forceDelete FoodCategory" . $e->getMessage());

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
