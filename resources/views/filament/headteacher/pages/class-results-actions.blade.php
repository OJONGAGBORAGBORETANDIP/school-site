<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="button" wire:click="generateReports" color="primary">
                Generate Reports for Class
            </x-filament::button>
            <x-filament::button type="button" wire:click="approveClassResults" color="success">
                Approve Class Results
            </x-filament::button>
        </div>
    </form>

    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <strong>Generate Reports:</strong> Computes averages, class size, class average, and position for all students in the selected class and term.<br>
        <strong>Approve Class Results:</strong> All subject reports for the class and term must be submitted first. After approval, teachers cannot edit marks.
    </p>
</x-filament-panels::page>
