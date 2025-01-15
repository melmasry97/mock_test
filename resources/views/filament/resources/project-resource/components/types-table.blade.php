@php
    $types = $getRecord()->types;
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type Name</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if($types->isEmpty())
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No types attached to this project
                    </td>
                </tr>
            @else
                @foreach($types as $type)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $type->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ Str::limit($type->description, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $type->categories->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <x-filament::button
                                size="sm"
                                icon="heroicon-m-star"
                                wire:click="evaluateCategories({{ $type->id }})"
                            >
                                Evaluate Categories
                            </x-filament::button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

@script
<script>
    $wire.on('evaluateCategories', async (typeId) => {
        const type = @js($types->keyBy('id'));
        const categories = type[typeId].categories;

        const modal = await $wire.mountFormComponentAction('types_table', 'evaluate_categories');

        if (!modal) return;

        modal.form = categories.map(category => ({
            name: category.name,
            weight: category.pivot?.weight ?? 0,
        }));
    });
</script>
@endscript
