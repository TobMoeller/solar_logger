<?php

return [
    'command' => [
        'python_script_path' => env('INVERTER_COMMAND_PYTHON_SCRIPT_PATH', base_path('python/inverter_command.py')),
    ],
    'monitor' => [
        'enabled' => env('INVERTER_MONITOR_ENABLED', true),
    ],
];
