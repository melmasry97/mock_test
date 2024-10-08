@extends('layouts.app')

@section('content')
    <h1>Tasks</h1>

    <h2>Repo Panel</h2>
    <ul>
        @foreach($tasks->where('state', TaskState::REPO)->merge($tasks->where('state', TaskState::IN_PROGRESS)) as $task)
            <li>
                {{ $task->title }}
                <a href="{{ route('tasks.edit', $task) }}">Edit</a>
            </li>
        @endforeach
    </ul>

    <h2>Backlog (Done Tasks)</h2>
    <ul>
        @foreach($tasks->where('state', TaskState::DONE) as $task)
            <li>{{ $task->title }}</li>
        @endforeach
    </ul>
@endsection
