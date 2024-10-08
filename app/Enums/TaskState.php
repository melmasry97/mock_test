<?php

namespace App\Enums;

enum TaskState: string
{
    case REPO = 'repo';
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public static function getLabels(): array
    {
        return [
            self::REPO->value => 'Repo',
            self::TODO->value => 'To Do',
            self::IN_PROGRESS->value => 'In Progress',
            self::DONE->value => 'Done',
        ];
    }
}
