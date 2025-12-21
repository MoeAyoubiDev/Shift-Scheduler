<?php
declare(strict_types=1);

return [
    'days' => [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ],
    'schedule_days' => [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
        'N/A',
    ],
    'shift_types' => ['AM', 'PM', 'MID', 'NIGHT'],
    'editable_shift_types' => ['AM', 'PM', 'MID', 'NIGHT', 'OFF', 'UNASSIGNED'],
    'schedule_options' => [
        '5x2' => 'Work 5 days / 2 off (9 hours)',
        '6x1' => 'Work 6 days / 1 off (7.5 hours)',
    ],
    'importance_levels' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'emergency' => 'Emergency',
    ],
];
