<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'background_jobs' => [
            'driver' => 'daily',
            'path' => storage_path('logs/background_jobs.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'background_jobs_errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/background_jobs_errors.log'),
            'level' => 'error',
            'days' => 14,
        ],
    ],
];