<?php

namespace Database\Seeders;

use App\Models\ClassSection;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            // Create at least one section per class (Section A)
            ClassSection::firstOrCreate(
                [
                    'school_class_id' => $class->id,
                    'name' => 'A',
                ],
                [
                    'label' => $class->code . 'A',
                    'capacity' => 40,
                ]
            );

            // For Primary 1 and Primary 2, create an additional section B
            if (in_array($class->level, [1, 2])) {
                ClassSection::firstOrCreate(
                    [
                        'school_class_id' => $class->id,
                        'name' => 'B',
                    ],
                    [
                        'label' => $class->code . 'B',
                        'capacity' => 40,
                    ]
                );
            }
        }
    }
}
