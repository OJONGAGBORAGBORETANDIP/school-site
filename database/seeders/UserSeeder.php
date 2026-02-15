<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $headteacherRole = Role::where('name', 'headteacher')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $parentRole = Role::where('name', 'parent')->first();

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // Create Headteacher
        $headteacher = User::firstOrCreate(
            ['email' => 'headteacher@school.com'],
            [
                'name' => 'Dr. John Headteacher',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $headteacher->roles()->syncWithoutDetaching([$headteacherRole->id]);

        // Create Teachers
        $teachers = [
            ['name' => 'Mrs. Sarah Teacher', 'email' => 'teacher1@school.com'],
            ['name' => 'Mr. James Teacher', 'email' => 'teacher2@school.com'],
            ['name' => 'Miss Mary Teacher', 'email' => 'teacher3@school.com'],
            ['name' => 'Mr. Peter Teacher', 'email' => 'teacher4@school.com'],
        ];

        foreach ($teachers as $teacherData) {
            $teacher = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
        }

        // Create Parents
        $parents = [
            ['name' => 'Mr. John Parent', 'email' => 'parent1@example.com', 'phone' => '+237 123456789'],
            ['name' => 'Mrs. Jane Parent', 'email' => 'parent2@example.com', 'phone' => '+237 987654321'],
            ['name' => 'Mr. David Parent', 'email' => 'parent3@example.com', 'phone' => '+237 555555555'],
        ];

        foreach ($parents as $parentData) {
            $parent = User::firstOrCreate(
                ['email' => $parentData['email']],
                [
                    'name' => $parentData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $parent->roles()->syncWithoutDetaching([$parentRole->id]);
        }
    }
}
