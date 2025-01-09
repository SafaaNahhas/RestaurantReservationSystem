<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Http\Requests\TableRequest\StoreTableRequest;
 use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest\FillterTabelRequest;
use App\Http\Requests\TableRequest\UpdateTableRequest;
use App\Http\Resources\Table\TableResource;
use App\Services\Tables\TableService;

class TableController extends Controller
{
    protected $tableService;
    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * get all tables
     * @param FillterTabelRequest request
     * @param int department_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(FillterTabelRequest $request,  $department_id)
    {
        $seat_count = $request->input('seat_count');
        $table_number = $request->input('table_number');
        $location = $request->input('location');
        $fillters = ["seat_count" => $seat_count, "table_number" => $table_number, "location" =>  $location];
        $tables = $this->tableService->allTables($fillters,  $department_id);
        return $this->paginated($tables, TableResource::class ,'Get All Tables', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * create a  new table
     * @param StoreTableRequest request
     * @param int department_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTableRequest $request,   $department_id)
    {
        $tableData = $request->validated();
        $table = $this->tableService->createTable($tableData, $department_id);
        return $this->success(['table' => $table], 'Created Table', 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * get a specified  table
     * @param int table_id
     * @param int department_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($department_id,   $table_id)
    {
        $table = $this->tableService->showTable($table_id,  $department_id);
        return $this->success(['table' => $table], 'Show Table', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * update a specified  table
     * @param UpdateTableRequest request
     * @param int department_id
     * @param int table_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTableRequest $request,   $department_id,  $table_id)
    {
        $tableData = $request->validated();
        $table = $this->tableService->updateTable($tableData, $table_id,  $department_id);
        return $this->success(['table' => $table], 'Updated Table',  200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * delete a specified  table
     * @param int department_id
     * @param int table_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($department_id,   $table_id)
    {
        $this->tableService->deleteTable($table_id,  $department_id);
        return $this->success(status: 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * get all deleted tables
     * @param FillterTabelRequest request
     * @param int department_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function allDeletedTables(FillterTabelRequest $request,   $department_id)
    {
        $seat_count = $request->input('seat_count');
        $table_number = $request->input('table_number');
        $location = $request->input('location');
        $fillters = ["seat_count" => $seat_count, "table_number" => $table_number, "location" =>  $location];
        $tables = $this->tableService->allDeletedTables($fillters,  $department_id);
        return $this->success(['tables' => $tables], 'Get All Deleted Tables', 200);
    }
    /**
     * Remove the specified resource from storage.
     */

    /**
     * restore a specified  table
     * @param int department_id
     * @param int table_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreTable($department_id, $table_id)
    {
        $table = $this->tableService->restoreTable($table_id,  $department_id);
        return $this->success(['table' => $table], 'Restore Table', 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    /**
     * force delete a specified  table
     * @param int department_id
     * @param int table_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteTable($department_id, $table_id)
    {
        $this->tableService->forceDeleteTable($table_id,  $department_id);
        return $this->success(status: 204);
    }
}