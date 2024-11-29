<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\FoodCategory;
use App\Services\FoodCategoryService as FoodCategoryService ;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\FoodCategoryRequest\StoreFoodCategoryRequest;
use App\Http\Requests\FoodCategoryRequest\UpdateFoodCategoryRequest;

class FoodCategoryController extends Controller
{
    /**
     * @var FoodCategoryService
     */
    protected $foodCategoryService;

     /**
     *  FoodCategoryController constructor
     * @param FoodCategory $FoodCategory
     */
    public function __construct(FoodCategoryService $foodCategoryService)
    {
        $this->foodCategoryService = $foodCategoryService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $foodCategory=$this->foodCategoryService->listCategory($perPage);
        return $this->success($foodCategory,'All Category retrieved successfully');
    }

    /**
     * Store a new foodCategory.
     *
     * @param StorebookRequest $request
     * @return JsonResponse
     */
    public function store(StoreFoodCategoryRequest $request)
    {
        $validatedrequest=$request->validated();
        $foodCategory=$this->foodCategoryService->createCategory($validatedrequest);
        return $this->success($foodCategory, 'foodCategory stored successfully.', 201);
    }

    /**
     * Show details of a specific foodcategory.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $category=$this->foodCategoryService->getCategory($id);
        return $this->success($category, 'foodCategory retrieved successfully.', 200);
    }

    /**
     * Update a specific foodCategory.
     *
     * @param UpdateFoodCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateFoodCategoryRequest $request, int $id)
    {
        $validatedRequest = $request->validated();
        $updatedfoodCategory=$this->foodCategoryService->updateFoodCategory($validatedRequest,$id);
        return $this->success($updatedfoodCategory, 'foodCategory uopdated successfully.', 200);
    }

    /**
     * Delete a specific FoodCategory.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $this->foodCategoryService->deleteFoodCategory($id);
        return $this->success([], 'foodCategory deleted successfully.', 200);
    }

     /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedFoodCategory = $this->foodCategoryService->trashedListFoodCategory($perPage);
        return $this->success($trashedFoodCategory);
    }

     /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restore($id)
    {
        $foodCategory = $this->foodCategoryService->restoreFoodCategory($id);
        return $this->success("FoodCategory restored Successfully");
    }


    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDelete($id)
    {
        $this->foodCategoryService->forceDeleteFoodCategory($id);
        return $this->success(null, "FoodCategory deleted Permanently");
    }
}
