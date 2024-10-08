<div class="modal-header">
    <h5 class="modal-title">Add Metrics for Task: {{ $task->title }}</h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <form id="metricForm" action="{{ route('tasks.metrics.store', $task) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Module Weight:</label>
            <input type="text" class="form-control" value="{{ $task->module_weight }}" disabled>
        </div>
        <div class="form-row">
            @foreach(['input1', 'input2', 'input3'] as $input)
                <div class="form-group col-md-3">
                    <label for="{{ $input }}">{{ ucfirst($input) }}:</label>
                    <select name="{{ $input }}" id="{{ $input }}" class="form-control" required>
                        <option value="">Select</option>
                        @foreach([1, 3, 4, 6, 8, 10] as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="form-group col-md-3">
                <label for="input4">Input 4:</label>
                <select name="input4" id="input4" class="form-control" required>
                    <option value="">Select</option>
                    @foreach([1, 3, 5, 7, 10] as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Matrix:</label>
            <div class="row">
                @for($i = 0; $i < 9; $i++)
                    <div class="col-md-4 mb-2">
                        <select name="matrix_values[]" class="form-control" required>
                            <option value="">Select</option>
                            @foreach([0, 1, 2, 3, 5] as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endfor
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    <button type="submit" form="metricForm" class="btn btn-primary">Submit</button>
</div>
