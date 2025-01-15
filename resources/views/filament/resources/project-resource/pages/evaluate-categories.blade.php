<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Save Evaluations
            </x-filament::button>
        </div>
    </form>

    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-600">
            Note: The sum of all weights must be less than or equal to 1.
            Each weight represents the relative importance of the category in this type.
        </p>
    </div>
</x-filament-panels::page>
