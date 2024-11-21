<?php

namespace App\Helpers;

use App\Services\BackgroundJobRunner;

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob(string $className, string $method, array $params = [], ?int $retries = null, int $priority = 0)
    {
        /** @var BackgroundJobRunner $runner */
        $runner = app(BackgroundJobRunner::class);
        return $runner->run($className, $method, $params, $retries, $priority);
    }
}