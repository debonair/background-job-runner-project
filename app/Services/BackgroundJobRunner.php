<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ReflectionClass;

class BackgroundJobRunner
{
    protected $maxRetries = 3;

    public function run(string $className, string $method, array $params = [], ?int $retries = null, int $priority = 0)
    {
        $jobId = $this->generateJobId();
        
        try {
            // Log job start with detailed context
            Log::channel('background_jobs')->info('Job Started', [
                'job_id' => $jobId,
                'class' => $className,
                'method' => $method,
                'params' => $params,
                'priority' => $priority,
                'status' => 'running',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            $reflectionClass = new ReflectionClass($className);
            $instance = $reflectionClass->newInstance();
            $result = $instance->{$method}(...$params);

            // Log successful completion
            Log::channel('background_jobs')->info('Job Completed', [
                'job_id' => $jobId,
                'class' => $className,
                'method' => $method,
                'status' => 'success',
                'result' => $result,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            return $result;
        } catch (\Throwable $e) {
            // Log error with detailed context
            Log::channel('background_jobs_errors')->error('Job Failed', [
                'job_id' => $jobId,
                'class' => $className,
                'method' => $method,
                'error' => $e->getMessage(),
                //'trace' => $e->getTraceAsString(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            throw $e;
        }
    }

    protected function generateJobId(): string
    {
        return 'job_' . uniqid() . '_' . time();
    }
}