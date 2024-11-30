<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\DishService as DishService ;
use App\Http\Requests\DishRequest\StoreDishRequest;
use App\Http\Requests\DishRequest\UpdateDishRequest;

class DishController extends Controller
{
    /**
     * @var DishService
     */
    protected $dishService;

     /**
     *  DishController constructor
     * @param DishService $DishService
     */
    public function __construct(DishService $dishService)
    {
        $this->dishService = $dishService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $Dish=$this->dishService->listDish($perPage);
        return $this->success($Dish,'All Dish retrieved successfully');
    }

    /**
     * Store a new Dish.
     *
     * @param StoreDishRequest $request
     * @return JsonResponse
     */
    public function store(StoreDishRequest $request)
    {
        $validatedrequest=$request->validated();
        $Dish=$this->dishService->createDish($validatedrequest);
        return $this->success($Dish, 'Dish stored successfully.', 201);
    }

    /**
     * Show details of a specific Dish.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $Dish=$this->dishService->getDish($id);
        return $this->success($Dish, 'Dish retrieved successfully.', 200);
    }

    /**
     * Update a specific Dish.
     *
     * @param UpdateDishRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateDishRequest $request, int $id)
    {
        $validatedRequest = $request->validated();
        $updatedDish=$this->dishService->updateDish($validatedRequest,$id);
        return $this->success($updatedDish, 'Dish uopdated successfully.', 200);
    }

    /**
     * Delete a specific Dish.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $this->dishService->deleteDish($id);
        return $this->success([], 'Dish deleted successfully.', 200);
    }

     /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedDish = $this->dishService->trashedListDish($perPage);
        return $this->success($trashedDish);
    }

     /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restore($id)
    {
        $Dish = $this->dishService->restoreDish($id);
        return $this->success("Dish restored Successfully");
    }


    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDelete($id)
    {
        $this->dishService->forceDeleteDish($id);
        return $this->success(null, "Dish deleted Permanently");
    }
}
