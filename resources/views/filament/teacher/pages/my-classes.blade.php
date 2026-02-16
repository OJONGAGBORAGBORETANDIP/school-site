<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->getClasses() as $class)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $class['label'] }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $class['class_name'] }}</p>
                    
                    @if($class['class_teacher'])
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <span class="font-semibold">Class Teacher:</span> {{ $class['class_teacher'] }}
                        </p>
                    @endif
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <span class="font-semibold">Students:</span> {{ $class['student_count'] }}
                    </p>
                    
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Subjects I Teach:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($class['subjects'] as $subject)
                                <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded text-xs">
                                    {{ $subject->name }} ({{ $subject->code }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
