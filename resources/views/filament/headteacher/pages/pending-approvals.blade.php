<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent class="space-y-6">
            {{ $this->form }}
        </form>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Select a class section and term above. Pending CA and Exam submissions will appear in the table below. Use <strong>View marks</strong> to see student marks before approving or rejecting. When rejecting, you will be asked to enter a reason so the teacher can correct and resubmit.
        </p>

        <div>
            {{ $this->table }}
        </div>
    </div>

    {{-- View marks overlay (shows when headteacher clicks "View marks") --}}
    @if($viewMarksType)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 dark:bg-gray-950/70" wire:key="view-marks-overlay">
            <div class="flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-4 dark:border-white/10 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Marks — {{ $viewMarksSubjectName }} ({{ $viewMarksType === 'ca' ? 'CA' : 'Exam' }})
                    </h3>
                    <x-filament::button size="sm" color="gray" wire:click="closeViewMarks">
                        Close
                    </x-filament::button>
                </div>
                @php $rows = $this->getViewMarksRows(); @endphp
                <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
                        <table class="w-full divide-y divide-gray-200 dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Student</th>
                                    @if($viewMarksType === 'ca')
                                        <th scope="col" class="px-4 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">CA mark (out of 30)</th>
                                    @else
                                        <th scope="col" class="px-4 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">Exam mark (out of 70)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10 dark:bg-white/5">
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-950 dark:text-white">{{ $row['student_name'] }}</td>
                                        @if($viewMarksType === 'ca')
                                            <td class="px-4 py-3 text-end text-sm text-gray-700 dark:text-gray-300">{{ $row['ca_mark'] ?? '—' }}</td>
                                        @else
                                            <td class="px-4 py-3 text-end text-sm text-gray-700 dark:text-gray-300">{{ $row['exam_mark'] ?? '—' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No marks to display.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
