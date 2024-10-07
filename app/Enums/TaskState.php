<?php

namespace App\Enums;

use Spatie\Enum\Enum;

final class TaskState extends Enum
{
    protected static function values(): array
    {
        return [
            'repo' => 'repo',
            'in_progress' => 'in_progress',
            'done' => 'done',
        ];
    }

    protected static function labels(): array
    {
        return [
            'repo' => 'Repository',
            'in_progress' => 'In Progress',
            'done' => 'Done',
        ];
    }

    // Custom method to get all values
    public static function getAllValues(): array
    {
        return array_column(self::toArray(), 'value');
    }
}
