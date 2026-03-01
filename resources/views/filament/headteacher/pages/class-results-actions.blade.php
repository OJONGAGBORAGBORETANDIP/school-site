<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}

        @php $classStatus = $this->getClassStatus(); @endphp
        @if($classStatus)
            <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status for selected class and term:</span>
                @if($classStatus === 'pending')
                    <span class="ml-2 inline-flex items-center rounded-md bg-amber-100 dark:bg-amber-900/30 px-2 py-1 text-xs font-medium text-amber-800 dark:text-amber-200">Pending Headmaster Approval</span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">All subject scores have been submitted by the teacher. You can approve (parents can then view report cards) or decline (teacher can correct scores).</p>
                @elseif($classStatus === 'approved')
                    <span class="ml-2 inline-flex items-center rounded-md bg-green-100 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-800 dark:text-green-200">Approved</span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Report cards for this class and term are visible to parents. To allow the teacher to correct scores, use Decline.</p>
                @else
                    <span class="ml-2 inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">Not ready</span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Not all subject reports are submitted yet. Teacher must submit scores for each subject first.</p>
                @endif
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button type="button" wire:click="generateReports" color="primary">
                Generate Reports for Class
            </x-filament::button>
            @if($classStatus === 'pending')
                <x-filament::button type="button" wire:click="approveClassResults" color="success">
                    Approve Class Results
                </x-filament::button>
                <x-filament::button type="button" wire:click="declineClassResults" color="danger" wire:confirm="Decline these results? The teacher will be able to correct the scores and resubmit.">
                    Decline (allow teacher to correct)
                </x-filament::button>
            @endif
            @if($classStatus === 'approved')
                <x-filament::button type="button" wire:click="declineClassResults" color="gray" wire:confirm="Decline these results? The teacher will be able to correct the scores and resubmit. Parents will no longer see report cards for this class and term until you approve again.">
                    Decline (return to teacher)
                </x-filament::button>
            @endif
        </div>
    </form>

    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <strong>Generate Reports:</strong> Computes averages, class size, class average, and position for all students in the selected class and term.<br>
        <strong>Approve:</strong> When all subject scores are submitted (Pending Headmaster Approval), approve so parents can view and download report cards.<br>
        <strong>Decline:</strong> Return results to the teacher so they can correct scores and resubmit.
    </p>
</x-filament-panels::page>
