<?php

namespace App\Services;

use App\Http\Resources\TableResource;
use App\Models\Department;
use App\Models\Table;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TableService
{
    /**
     * get all  tables
     * @param array  $fillters 
     * @param int department_id
     * @return  LengthAwarePaginator  tables 
     */
    public function allTables(array $fillters, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $tables = Table::byTableNumber($fillters['table_number'])
                ->bySeatCount($fillters['seat_count'])
                ->byLocation($fillters['location'])
                ->paginate(4);
            return $tables;
        } catch (ModelNotFoundException $e) {
            Log::error("error in  get all tables" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any relation",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in get all tables" . $e->getMessage());

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
     * create a new table
     * 
     * @param array  $tableData 
     * @param int department_id
     * @return TableResource  table
     */
    public function createTable(array $tableData, $department_id)
    {
        try {
            $department = Department::findOrFail($department_id);
            $table = Table::create([
                'table_number' => $tableData['table_number'],
                'location' => $tableData['location'],
                'seat_count' => $tableData['seat_count'],
                'department_id' => $department->id
            ]);
            $table = TableResource::make($table);
            return $table;
        } catch (ModelNotFoundException $e) {
            Log::error("error in create a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in create a table" . $e->getMessage());

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
     * show a table
     * 
     * @param int table_id 
     * @param int department_id
     * @return TableResource  table
     */

    public function showTable($table_id, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $table = Table::findOrFail($table_id);
            $table = TableResource::make($table);
            return $table;
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in get a table" . $e->getMessage());

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
     * update a table
     * @param array tableData
     * @param int table_id
     * @param int department_id
     * @return TableResource  table
     */
    public function updateTable(array $tableData, $table_id, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $table = Table::findOrFail($table_id);

            $table->update([
                'table_number' => $tableData['table_number'],
                'location' => $tableData['location'],
                'seat_count' => $tableData['seat_count'],
            ]);
            $table = TableResource::make(Table::findOrFail($table->id));
            return $table;
        } catch (ModelNotFoundException $e) {
            Log::error("error in update a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in update a table" . $e->getMessage());

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
     * delete a table
     * @param int table_id
     * @param int department_id
     */
    public function deleteTable($table_id, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $table = Table::findOrFail($table_id);
            $table->delete();
        } catch (ModelNotFoundException $e) {
            Log::error("error in delete a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in delete a table" . $e->getMessage());

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
     * show all delered tables
     * @param array fillters
     * @param int department_id
     * @return LengthAwarePaginator  tables
     */
    public function allDeletedTables($fillters, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $tables = Table::onlyTrashed()
                ->byTableNumber($fillters['table_number'])
                ->bySeatCount($fillters['seat_count'])
                ->byLocation($fillters['location'])
                ->paginate(4);
            $tables = TableResource::collection($tables);
            return $tables;
        } catch (ModelNotFoundException $e) {
            Log::error("error in get all deleted tables" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in get all deleted tables" . $e->getMessage());

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
     * restore a table
     * @param int  table_id
     * @param int department_id
     * @return LengthAwarePaginator  tables
     */
    public function restoreTable($table_id, $department_id)
    {
        try {
            Department::findOrFail($department_id);

            $table = Table::onlyTrashed()->findOrFail($table_id);
            $table->restore();
            $table = TableResource::make($table);
            return $table;
        } catch (ModelNotFoundException $e) {
            Log::error("error in restore a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in restore a table" . $e->getMessage());

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
     * force delete a table  
     * @param int  table_id
     * @param int department_id
     */
    public function forceDeleteTable($table_id, $department_id)
    {
        try {
            Department::findOrFail($department_id);
            $table = Table::findOrFail($table_id);

            $table->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error("error in force delete  a table" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in force delete  a table" . $e->getMessage());

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