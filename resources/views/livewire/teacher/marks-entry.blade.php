<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Select Class, Subject & Term</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Class Section</label>
                <select
                    wire:model.live="selectedClassSection"
                    class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                    <option value="" class="text-lg">Select a class section</option>
                    @foreach($classSections as $section)
                        <option value="{{ $section['id'] }}" class="text-xl">{{ $section['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                <select
                    wire:model.live="selectedSubject"
                    class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    {{ empty($subjects) ? 'disabled' : '' }}
                >
                    <option value="">Select a subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject['id'] }}">{{ $subject['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Term</label>
                <select
                    wire:model.live="selectedTerm"
                    class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                    <option value="">Select a term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term['id'] }}">{{ $term['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(!empty($reviewSummary))
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Result review</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Review all entered student results for this class and term before submission. Submit when marks are finalized.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Students with marks</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total students</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($reviewSummary as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row['label'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $row['entered'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $row['total'] }}</td>
                                <td class="px-4 py-3">
                                    @if($row['status'] === 'submitted')
                                        <span class="inline-flex items-center rounded-md bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-800 dark:text-green-200">Submitted</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">Draft</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($marks))
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        @if($isSubmitted)
                            Previously entered scores (submitted for approval)
                        @elseif(!$canEdit)
                            Past term – view only
                        @else
                            Enter marks
                        @endif
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        CA (Continuous Assessment) out of 30, Exam out of 70. Final score = CA + Exam (out of 100). Grade and remark are assigned from the scale below.
                    </p>
                </div>
                @if($isSubmitted)
                    <span class="inline-flex items-center rounded-md bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-sm font-medium text-amber-800 dark:text-amber-200">Submitted – view only</span>
                @elseif(!$canEdit)
                    <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300">Past term – read only</span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admission No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CA (out of 30)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exam (out of 70)</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Comment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($marks as $index => $mark)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $mark['student_name'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $mark['admission_number'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(!$canEdit)
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $mark['ca_mark'] !== '' && $mark['ca_mark'] !== null ? number_format((float)$mark['ca_mark'], 2) : '-' }}</span>
                                    @else
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="30"
                                            wire:model.live="marks.{{ $index }}.ca_mark"
                                            class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        />
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(!$canEdit)
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $mark['exam_mark'] !== '' && $mark['exam_mark'] !== null ? number_format((float)$mark['exam_mark'], 2) : '-' }}</span>
                                    @else
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="70"
                                            wire:model.live="marks.{{ $index }}.exam_mark"
                                            class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        />
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $mark['total_mark'] ? number_format($mark['total_mark'], 2) : '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $mark['grade'] ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(!$canEdit)
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $mark['teacher_comment'] ?? '-' }}</span>
                                    @else
                                        <input
                                            type="text"
                                            wire:model="marks.{{ $index }}.teacher_comment"
                                            placeholder="Optional"
                                            class="w-full min-w-[8rem] rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($canEdit)
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                    @if(!empty($validationErrors))
                        <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-400">
                            <strong>Validation failed:</strong> CA must be 0–30, Exam must be 0–70. Fix the following before saving or submitting:
                            <ul class="mt-1 list-disc list-inside">
                                @foreach($validationErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Validation:</strong> CA 0–30, Exam 0–70. Both buttons check these limits before saving.
                        </div>
                    @endif
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="saveAsDraft"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Save as draft
                        </button>
                        <button
                            type="button"
                            wire:click="save"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Save
                        </button>
                    </div>
                </div>
            @endif

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-lg">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">How scores are calculated</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <strong>Final score</strong> = CA + Exam (no conversion: CA is out of 30, Exam is out of 70; total is out of 100).
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mb-3">
                    Enter CA (0–30) and Exam (0–70). The system adds them to get the total (0–100) and assigns a grade from the scale below.
                </p>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Grading scale (A–F, based on total out of 100)</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 dark:border-gray-600 rounded-md">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total mark range</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Grade</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($gradingScales as $scale)
                                <tr>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $scale->min_mark }} – {{ $scale->max_mark }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $scale->grade }}</td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $scale->remark }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

