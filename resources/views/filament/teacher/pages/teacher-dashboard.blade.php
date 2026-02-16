<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">My Classes</h3>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ \App\Models\TeacherAssignment::where('teacher_id', auth()->id())->distinct('class_section_id')->count() }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Subjects</h3>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ \App\Models\TeacherAssignment::where('teacher_id', auth()->id())->distinct('subject_id')->count() }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Total Students</h3>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ \App\Models\Enrollment::whereIn('class_section_id', function($query) {
                        $query->select('class_section_id')
                            ->from('teacher_assignments')
                            ->where('teacher_id', auth()->id());
                    })->where('is_active', true)->count() }}
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
