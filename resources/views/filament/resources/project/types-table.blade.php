@php
    use App\Models\ProjectTypeCategoryEvaluation;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    $record = $getRecord();
    $types = $record->types;

    // Pre-calculate evaluations for all types to avoid N+1 queries
    $evaluations = ProjectTypeCategoryEvaluation::where('project_id', $record->id)
        ->where('user_id', Auth::id())
        ->pluck('type_id')
        ->toArray();
@endphp

<div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
    <table class="w-full text-start divide-y table-auto">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 text-start">Type Name</th>
                <th class="px-4 py-2 text-start">Description</th>
                <th class="px-4 py-2 text-center">Categories</th>
                <th class="px-4 py-2 text-center">Status</th>
                <th class="px-4 py-2 text-end">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($types as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 align-middle">{{ $type->name }}</td>
                    <td class="px-4 py-2 align-middle">{{ Str::limit($type->description, 50) }}</td>
                    <td class="px-4 py-2 align-middle text-center">
                        @if($type->categories->count() > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $type->categories->count() }} Categories
                            </span>
                        @else
                            <span class="text-gray-500">No categories</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-middle text-center">
                        @if($type->categories->count() > 0 && in_array($type->id, $evaluations))
                            <x-heroicon-s-check-circle class="w-5 h-5 text-success-500 inline"/>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-middle text-end">
                        <div class="flex items-center justify-end gap-2">
                            @if($type->categories->count() > 0 && !in_array($type->id, $evaluations) && !$type->evaluation_end_time)
                                <x-filament::button
                                    size="sm"
                                    icon="heroicon-m-star"
                                    wire:click="evaluateCategories({{ $type->id }})"
                                >
                                    Evaluate
                                </x-filament::button>
                            @endif
                            <x-filament::button
                                size="sm"
                                color="danger"
                                wire:click="detachType({{ $type->id }})"
                            >
                                Detach
                            </x-filament::button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-gray-500">
                        No types attached
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
