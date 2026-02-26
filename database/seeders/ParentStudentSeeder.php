<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parents = User::whereHas('roles', function ($query) {
            $query->where('name', 'parent');
        })->get();

        $students = Student::all();

        if ($parents->isEmpty() || $students->isEmpty()) {
            $this->command->warn('No parents or students found. Please run UserSeeder and StudentSeeder first.');
            return;
        }

        // First, create parent records linked to users
        $parentRecords = [];
        foreach ($parents as $index => $user) {
            $parentRecord = ParentModel::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => explode(' ', $user->name)[0] ?? 'Parent',
                    'last_name' => explode(' ', $user->name)[1] ?? 'User',
                    'email' => $user->email,
                    'phone' => '+237 ' . (123456789 + $index),
                    'relationship' => $index === 1 ? 'mother' : 'father',
                ]
            );
            $parentRecords[] = $parentRecord;
        }

        // Link parents to students via pivot table (with relationship for guardian dropdowns)
        // Parent 1 (father) -> Student 1 & 2
        if (isset($parentRecords[0]) && isset($students[0])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parentRecords[0]->id, 'student_id' => $students[0]->id],
                ['relationship' => $parentRecords[0]->relationship === 'mother' ? 'mother' : 'father']
            );
        }
        if (isset($parentRecords[0]) && isset($students[1])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parentRecords[0]->id, 'student_id' => $students[1]->id],
                ['relationship' => $parentRecords[0]->relationship === 'mother' ? 'mother' : 'father']
            );
        }
        // Parent 2 (mother) -> Student 3
        if (isset($parentRecords[1]) && isset($students[2])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parentRecords[1]->id, 'student_id' => $students[2]->id],
                ['relationship' => $parentRecords[1]->relationship === 'mother' ? 'mother' : 'father']
            );
        }
        // Parent 3 -> Student 4 (as other guardian)
        if (isset($parentRecords[2]) && isset($students[3])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parentRecords[2]->id, 'student_id' => $students[3]->id],
                ['relationship' => 'guardian']
            );
        }
    }
}
