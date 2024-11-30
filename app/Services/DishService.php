<?php

namespace App\Services;



use App\Models\Dish;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\Auth;

class DishService{


    /**
     * List all Dish
     */
    public function listDish($per_page)
    {
        try {
            return Dish::with('category')->paginate($per_page);
        } catch (Exception $e) {
         Log::error('Error in Get all Dishes'. $e->getMessage());
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
     * Create a new Dish.
     *
     * @param array $Dish
     * @return \App\Models\Dish
     */
    public function createDish(array $data)
    {
        try {
            // Create a new Dish record with the provided data
            return Dish::create([
                'name'=> $data['name'],
                'description'=> $data['description'] ?? null, 
                'category_id'=>$data['category_id'], 
            ]);
        } catch (Exception $e) {
          Log::error('Error creating Dish: ' . $e->getMessage());
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
     * Get the details of a specific Dish by its ID.
     *
     * @param int $id
     * @return \App\Models\Dish
     */
    public function getDish(int $id)
    {
        try {
            // Find the Dish by ID or fail with a 404 error if not found
            $dish= Dish::findOrFail($id);
            return $dish->load('category');
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        
        } catch (Exception $e) {
            Log::error("error in get a Dish" . $e->getMessage());

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
    public function updateDish(array $data, int $id)
    {
        try {
            // Find the dish by ID or fail with a 404 error if not found
            $dish = Dish::findOrFail($id);

            // Update the dish with the provided data, filtering out null values
            $dish->update(array_filter([
                'name'=> $data['name'] ?? $dish->Dish_name,
                'description'=> $data['description'] ?? $dish->description,
                'category_id'=> $data['category_id'] ?? $dish->category_id,
            ]));
           
            // Return the updated dish
            return $dish;
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
            Log::error("error in update Dish" . $e->getMessage());

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
     * Delete a specific Dish by its ID.
     *
     * @param int $id
     * @return void
     */
    public function deleteDish(int $id)
    {
        try {
            // Find the Dish by ID or fail with a 404 error if not found
            $Dish = Dish::findOrFail($id);

            // Delete the Dish
            $Dish->delete();
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
            Log::error("error in delete a Dish" . $e->getMessage());

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
    public function trashedListDish($perPage)
    {
        try {
            return Dish::onlyTrashed()->paginate($perPage);
        }  catch (Exception $e) {
            Log::error("error in display list of trashed Dish" . $e->getMessage());

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
    public function restoreDish($id)
    {
        try {
            $$Dish = Dish::onlyTrashed()->findOrFail($id);
            $$Dish->restore();
            return $$Dish;
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
            Log::error("error in restore a Dish" . $e->getMessage());

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
    public function forceDeleteDish($id)
    {
        try {
            $Task = Dish::onlyTrashed()->findOrFail($id);

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
            Log::error("error  in forceDelete Dish" . $e->getMessage());

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