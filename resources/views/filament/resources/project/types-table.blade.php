@php
    $record = $getRecord();
    $types = $record->types;
@endphp

<div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
    <table class="w-full text-start divide-y table-auto">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 text-start">Type Name</th>
                <th class="px-4 py-2 text-start">Description</th>
                <th class="px-4 py-2 text-start">Categories</th>
                <th class="px-4 py-2 text-end">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($types as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 align-middle">{{ $type->name }}</td>
                    <td class="px-4 py-2 align-middle">{{ Str::limit($type->description, 50) }}</td>
                    <td class="px-4 py-2 align-middle">{{ $type->categories->count() }}</td>
                    <td class="px-4 py-2 align-middle text-end">
                        <div class="flex items-center justify-end gap-2">
                            <x-filament::button
                                size="sm"
                                icon="heroicon-m-star"
                                wire:click="evaluateCategories({{ $type->id }})"
                            >
                                Evaluate
                            </x-filament::button>

                            <x-filament::button
                                size="sm"
                                color="danger"
                                icon="heroicon-m-x-mark"
                                wire:click="detachType({{ $type->id }})"
                            >
                                Detach
                            </x-filament::button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                        No types attached to this project
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
