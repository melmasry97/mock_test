@extends('layouts.app')

@section('content')
    <h1>Edit Task: {{ $task->title }}</h1>

    <!-- Existing task edit form -->

    @if($task->state === TaskState::REPO || $task->state === TaskState::IN_PROGRESS)
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#metricModal">
            Add Metrics
        </button>

        <!-- Metric Modal -->
        <div class="modal fade" id="metricModal" tabindex="-1" role="dialog" aria-labelledby="metricModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#metricModal').on('show.bs.modal', function (event) {
            var modal = $(this);
            modal.find('.modal-content').load("{{ route('tasks.metrics.create', $task) }}");
        });
    });
</script>
@endpush
