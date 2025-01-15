@php
    $record = $getRecord();
@endphp

<div class="flex items-center gap-2 justify-end">
    <x-filament::button
        size="sm"
        icon="heroicon-m-star"
        wire:click="evaluateCategories({{ $record->id }})"
    >
        Evaluate Categories
    </x-filament::button>

    <x-filament::button
        size="sm"
        color="danger"
        icon="heroicon-m-x-mark"
        wire:click="detachType({{ $record->id }})"
    >
        Detach
    </x-filament::button>
</div>
