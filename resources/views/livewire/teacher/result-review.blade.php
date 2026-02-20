<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-4 border border-gray-200 dark:border-gray-700">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Select class and term</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Class section</label>
                <select
                    wire:model.live="selectedClassSection"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                    <option value="">Select a class section</option>
                    @foreach($classSections as $section)
                        <option value="{{ $section['id'] }}">{{ $section['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Term</label>
                <select
                    wire:model.live="selectedTerm"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
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
                    Enter or edit marks for each subject via Marks entry, then submit when finalized.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Students with marks</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
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
                                <td class="px-4 py-3 space-y-1">
                                    <a href="{{ route('teacher.marks-entry', ['class_section' => $selectedClassSection, 'term' => $selectedTerm, 'subject' => $row['subject_id']]) }}" class="block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Marks entry
                                    </a>
                                    @if($row['status'] === 'draft')
                                        <button type="button" wire:click="submitSubjectForApproval({{ $row['subject_id'] }})" wire:confirm="Submit this subject's results for head teacher approval? You will not be able to edit them afterwards."
                                            class="block text-sm text-amber-600 dark:text-amber-400 hover:underline text-left">
                                            Submit finalized results to head teacher
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
