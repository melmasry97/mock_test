@if(count($types) === 0)
    <div class="text-gray-500">No types found for this project.</div>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($types as $type)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $type['name'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($type['description'], 50) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="space-y-1">
                                @foreach($type['categories'] as $category)
                                    <div class="flex items-center justify-between">
                                        <span>{{ $category['name'] }}</span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Weight: {{ $category['weight'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No types attached to this project
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
