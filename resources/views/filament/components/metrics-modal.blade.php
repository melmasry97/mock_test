<x-filament::modal>
    <x-slot name="header">
        Add Metrics for Task: {{ $task->name }}
    </x-slot>

    <x-filament-forms::form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-slot name="footer">
            <x-filament::button type="submit">
                Submit
            </x-filament::button>
        </x-slot>
    </x-filament-forms::form>
</x-filament::modal>
