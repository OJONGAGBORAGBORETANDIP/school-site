<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'date_of_birth' => '2018-05-15',
                'gender' => 'female',
                'admission_number' => 'STU' . str_pad(1, 5, '0', STR_PAD_LEFT),
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'date_of_birth' => '2018-08-20',
                'gender' => 'male',
                'admission_number' => 'STU' . str_pad(2, 5, '0', STR_PAD_LEFT),
            ],
            [
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'date_of_birth' => '2017-11-10',
                'gender' => 'male',
                'admission_number' => 'STU' . str_pad(3, 5, '0', STR_PAD_LEFT),
            ],
            [
                'first_name' => 'Diana',
                'last_name' => 'Williams',
                'date_of_birth' => '2017-03-25',
                'gender' => 'female',
                'admission_number' => 'STU' . str_pad(4, 5, '0', STR_PAD_LEFT),
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Davis',
                'date_of_birth' => '2016-07-12',
                'gender' => 'female',
                'admission_number' => 'STU' . str_pad(5, 5, '0', STR_PAD_LEFT),
            ],
            [
                'first_name' => 'Frank',
                'last_name' => 'Miller',
                'date_of_birth' => '2016-09-30',
                'gender' => 'male',
                'admission_number' => 'STU' . str_pad(6, 5, '0', STR_PAD_LEFT),
            ],
        ];

        foreach ($students as $studentData) {
            Student::firstOrCreate(
                ['admission_number' => $studentData['admission_number']],
                $studentData
            );
        }
    }
}
