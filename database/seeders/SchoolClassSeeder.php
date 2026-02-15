<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['name' => 'Primary 1', 'code' => 'P1', 'level' => 1],
            ['name' => 'Primary 2', 'code' => 'P2', 'level' => 2],
            ['name' => 'Primary 3', 'code' => 'P3', 'level' => 3],
            ['name' => 'Primary 4', 'code' => 'P4', 'level' => 4],
            ['name' => 'Primary 5', 'code' => 'P5', 'level' => 5],
            ['name' => 'Primary 6', 'code' => 'P6', 'level' => 6],
        ];

        foreach ($classes as $class) {
            SchoolClass::firstOrCreate(
                ['code' => $class['code']],
                $class
            );
        }
    }
}
