<x-filament-panels::page>
    <form wire:submit="saveMarks">
        <x-filament::section>
            <x-slot name="heading">
                Select Class, Subject, and Term
            </x-slot>
            
            {{ $this->form }}
        </x-filament::section>

        @if(!empty($marks))
            <x-filament::section>
                <x-slot name="heading">
                    Enter Marks
                </x-slot>
                <x-slot name="description">
                    Enter Sequence marks and Exam marks for each student. Total marks, grade, and remark will be calculated automatically.
                </x-slot>

                <div class="overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full align-middle">
                        <table class="fi-ta-table w-full divide-y divide-gray-200 dark:divide-white/5">
                            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Student
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Admission No.
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Sequence Mark
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Exam Mark
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Total
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Grade
                                            </span>
                                        </span>
                                    </th>
                                    <th class="fi-ta-header-cell px-3 py-3.5 sm:px-6 text-start">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Comment
                                            </span>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap">
                                @foreach($marks as $index => $mark)
                                    <tr class="fi-ta-row">
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm text-gray-950 dark:text-white">
                                                    {{ $mark['student_name'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $mark['admission_number'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        wire:model.live="marks.{{ $index }}.ca_mark"
                                                        class="w-24"
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        wire:model.live="marks.{{ $index }}.exam_mark"
                                                        class="w-24"
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                    {{ $mark['total_mark'] ? number_format($mark['total_mark'], 2) : '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                    {{ $mark['grade'] ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="text"
                                                        wire:model="marks.{{ $index }}.teacher_comment"
                                                        placeholder="Optional comment"
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <x-slot name="footerActions">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="sm"
                    >
                        Save Marks
                    </x-filament::button>
                </x-slot>
            </x-filament::section>
        @endif
    </form>
</x-filament-panels::page>
