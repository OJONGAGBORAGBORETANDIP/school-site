@extends('layouts.settings-layout')

@section('settingsContent')
<div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Appearance') }}</h3>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">{{ __('Choose how the app looks.') }}</p>
    <flux:radio.group variant="segmented" x-model="$flux.appearance">
        <flux:radio value="light">Light</flux:radio>
        <flux:radio value="dark">Dark</flux:radio>
        <flux:radio value="system">System</flux:radio>
    </flux:radio.group>
</div>
@endsection