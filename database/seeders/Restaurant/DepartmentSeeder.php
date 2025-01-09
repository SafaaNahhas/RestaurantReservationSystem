<?php
namespace Database\Seeders\Restaurant;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        //create departmen
        $Departments = [
            [
              'name' => 'department Indoor ',
                'description' => 'department Indoor department Indoor',
                'manager_id' => 1,
              
            ],
            [
                'name' => 'department Outdoor  ',
                'description' => 'department Outdoor department Outdoor',
                'manager_id' => 2,
                
            ],
      
         
        ];
        
        foreach ($Departments as $Department) {
            Department::create($Department);
        }
    }
}
