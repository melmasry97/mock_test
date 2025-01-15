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

<div class="space-y-4">
    @foreach($types as $type)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $type->name }}</h3>
                    <p class="text-sm text-gray-500">{{ Str::limit($type->description, 100) }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    @if(in_array($type->id, $evaluations))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Evaluated
                        </span>
                    @else
                        @if(!$type->evaluation_end_time || now()->lt($type->evaluation_end_time))
                            <a href="{{ route('filament.user.resources.projects.evaluate-categories', ['record' => request()->route('record'), 'typeId' => $type->id]) }}"
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Evaluate Categories
                            </a>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Evaluation Ended
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <div class="text-sm font-medium text-gray-700 mb-2">Categories ({{ $type->categories->count() }})</div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($type->categories as $category)
                        <div class="bg-gray-50 rounded p-3">
                            <div class="font-medium">{{ $category->name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($category->description, 100) }}</div>
                            @php
                                $evaluation = $category->evaluations->where('user_id', auth()->id())->first();
                            @endphp
                            <div class="mt-2 flex justify-between items-center text-sm">
                                <span class="text-gray-500">Your Weight:</span>
                                <span class="font-medium {{ $evaluation ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $evaluation ? number_format($evaluation->weight, 2) : 'Not evaluated' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
