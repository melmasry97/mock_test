<div class="flex items-center justify-between gap-4">
    <div>
        <h3 class="text-lg font-medium">{{ $title }}</h3>
    </div>
    <div class="flex items-center gap-4">
        {{ $this->table->getSearchForm() }}
        <div class="flex items-center gap-2">
            @foreach ($actions ?? [] as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>
</div>
