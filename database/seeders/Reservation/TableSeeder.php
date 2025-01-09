<?php

namespace Database\Seeders\Reservation;

use App\Models\Table;
use App\Models\Department;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all departments
        $departments = Department::all();

        // Sample data for tables
        $tables = [
            [
                'table_number' => 'T1',
                'location' => 'Near Window',
                'seat_count' => 4,
            
            ],
            [
                'table_number' => 'T2',
                'location' => 'Center Hall',
                'seat_count' => 6,
            
            ],
            [
                'table_number' => 'T3',
                'location' => 'Near Entrance',
                'seat_count' => 2,
            
            ],
            [
                'table_number' => 'T4',
                'location' => 'VIP Room',
                'seat_count' => 10,
            
            ],
            [
                'table_number' => 'T5',
                'location' => 'Terrace',
                'seat_count' => 4,
            
            ],
        ];

        // Insert data into the database
        foreach ($tables as $table) {
            Table::firstOrCreate(
                ['table_number' => $table['table_number']], // Unique constraint
                $table
            );
        }
    }
}
