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
              'name' => 'Families ',
                'description' => 'Special section for families',
                'manager_id' => 1,
              
            ],
            [
                'name' => 'Girls ',
                'description' => 'Special section for girls.',
                'manager_id' => 2,
                
            ],
      
         
        ];
        
        foreach ($Departments as $Department) {
            Department::create($Department);
        }
    }
}
