@php
use App\Models\ProjectTypeCategoryEvaluation;
use Illuminate\Support\Str;

// Pre-calculate evaluations to avoid N+1 queries
$evaluations = ProjectTypeCategoryEvaluation::where('project_id', request()->route('record'))
    ->where('user_id', auth()->id())
    ->get()
    ->pluck('type_id')
    ->toArray();
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Value</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluations</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if(count($categories) === 0)
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No categories found.
                    </td>
                </tr>
            @else
                @foreach($categories as $category)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category['name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($category['description'], 100) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category['average_value'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category['evaluation_count'] }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
