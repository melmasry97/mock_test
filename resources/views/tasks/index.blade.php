@extends('layouts.app')

@section('content')
    <h1>Tasks</h1>

    <h2>To Do and In Progress Tasks</h2>
    <ul>
        @foreach($tasks->whereIn('state', [TaskState::TODO, TaskState::IN_PROGRESS]) as $task)
            <li>
                {{ $task->title }}
                <a href="{{ route('tasks.edit', $task) }}">Edit</a>
            </li>
        @endforeach
    </ul>

    <h2>Done Tasks</h2>
    <ul>
        @foreach($tasks->where('state', TaskState::DONE) as $task)
            <li>{{ $task->title }}</li>
        @endforeach
    </ul>
@endsection
