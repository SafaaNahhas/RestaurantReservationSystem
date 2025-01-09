<?php

namespace Tests\Feature;

use App\Enums\RoleUser;
use App\Models\Department;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
 use Tests\TestCase;
use Illuminate\Support\Str;

class TableTest extends TestCase
{
    use DatabaseTransactions;
    public function startCode()
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleUser::Admin);
        $token = JWTAuth::fromUser($admin);


        $manager1 = User::factory()->create();
        $manager1->assignRole(RoleUser::ReservationManager);

        $department = Department::create([
            'name' => 'Kitchen',
            'description' => 'Responsible for food preparation.',
            'manager_id' => $manager1->id,
        ]);

        return [
            'token' => $token,
            'department' => $department,
        ];
    }
    /**
     * A basic feature test example.
     */
    public function test_get_all_tables(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        Table::factory()->count(5)->create();

        $response = $this->get("api/departments/$department->id/tables", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }
    public function test_get_all_tables_department_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        Table::factory()->count(5)->create();
        $x = ($department->id) + 100;
        $response = $this->get("api/departments/$x/tables", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);

    }
    public function test_show_one_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $response = $this->get("api/departments/$department->id/tables/$table->id", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);

    }  
    public function test_show_one_table_department_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $x = ($department->id) + 100;
        $response = $this->get("api/departments/$x/tables", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);

    } 
    public function test_show_one_table_table_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $x=($table->id)+100;
         $response = $this->get("api/departments/$department->id/tables/$x", [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);

    }  
    public function test_create_new_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $response = $this->post(
            "api/departments/$department->id/tables",
            [
                'table_number' =>Str::random(30) ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(201);
    }  

    public function test_create_new_table_department_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $response = $this->post(
            "api/departments/$department->id/tables",
            [
                'table_number' =>Str::random(30) ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }  

    public function test_create_new_table_deplucted_table_number(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table_number=Str::random(30);
        $this->post(
            "api/departments/$department->id/tables",
            [
                'table_number' =>$table_number ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response = $this->post(
            "api/departments/$department->id/tables",
            [
                'table_number' =>$table_number ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
                'department_id' =>  $department->id 
            ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }  


    public function test_update_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();

        $response = $this->put(
            "api/departments/$department->id/tables/$table->id",
            [
                'table_number' =>Str::random(30) ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(200);
    }  
    public function test_update_table_table_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();

        $x=($table->id)+100;
        $response = $this->put(
            "api/departments/$department->id/tables/$x",
            [
                'table_number' =>Str::random(30) ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    }  

    public function test_update_table_deplucted_table_number(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table1 =Table::factory()->create();
        $table2 =Table::factory()->create();

        $response = $this->put(
            "api/departments/$department->id/tables/$table2->id",
            [
                'table_number' =>$table1->table_number ,
                'location' =>Str::random(30) ,
                'seat_count' =>  random_int(1, 10),
             ],
            [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(422);
    }  

    public function test_delete_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();

        $response = $this->delete(
            "api/departments/$department->id/tables/$table->id",

            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(204);
    }
    
    public function test_delete_table_table_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        
        $x=($table->id)+100;

        $response = $this->delete(
            "api/departments/$department->id/tables/$x",
            headers: [
                "Authorization" => 'Bearer ' . $token,
            ]
        );
        $response->assertStatus(404);
    } 

    
    public function test_get_all_deleted_tables(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        Table::factory()->count(5)->create();
        $tables=Table::all();
        foreach($tables as $table){
            $table->delete();
        }
        $response = $this->get("api/departments/$department->id/allDeletedTables", headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_restore_deleted_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $table->delete();
        $response = $this->post("api/departments/$department->id/tables/$table->id/restore",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(200);
    }

    public function test_restore_deleted_table_table_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $table->delete();

        $x=( $table->id)+100;
        $response = $this->post("api/departments/$department->id/tables/$x/restore",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }


    public function test_final_delete_table(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $this->delete("api/departments/$department->id/tables/$table->id/delete",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response = $this->delete("api/departments/$department->id/tables/$table->id/forceDelete",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(204);
    }

    public function test_final_delete_table_table_not_found(): void
    {
        $data = $this->startCode();
        $department = $data['department'];
        $token = $data['token'];

        $table =Table::factory()->create();
        $this->delete("api/departments/$department->id/tables/$table->id/delete",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $x=($table->id)+100;
        $response = $this->delete("api/departments/$department->id/tables/$x/forceDelete",
        headers: [
            "Authorization" => 'Bearer ' . $token,
        ]);
        $response->assertStatus(404);
    }
}
