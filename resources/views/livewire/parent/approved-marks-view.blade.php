<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                @if($type === 'ca')
                    View CA results
                @else
                    View exam results
                @endif
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select school year and term to see approved {{ $type === 'ca' ? 'Continuous Assessment' : 'exam' }} marks for your children.</p>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="school-year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School year</label>
                <select
                    id="school-year"
                    wire:model.live="schoolYearId"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                    <option value="">Select school year</option>
                    @foreach($schoolYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Term</label>
                <select
                    id="term"
                    wire:model.live="termId"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    @if(!$schoolYearId) disabled @endif
                >
                    <option value="">Select term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($schoolYearId && $termId)
        <div class="px-6 pb-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                @if($type === 'ca')
                                    CA mark (out of 30)
                                @else
                                    Exam mark (out of 70)
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['student_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['subject_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ $row['mark'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No approved {{ $type === 'ca' ? 'CA' : 'exam' }} results for this term yet. Results will appear here once the headteacher approves them.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="px-6 pb-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Select a school year and term above to view results.</p>
        </div>
        @endif
    </div>
</div>
