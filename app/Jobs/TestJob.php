<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class TestJob
{
    public function processData($data)
    {
        // Simulate some processing
        Log::info('Processing data', ['data' => $data]);
        
        // Optional: Simulate potential failure
        if (rand(0, 1) === 0) {
            throw new \Exception("Random processing error");
        }

        return "Processed: " . json_encode($data);
    }
}

// In a controller or route
$result = runBackgroundJob(
    TestJob::class, 
    'processData', 
    [['key' => 'value']], // Parameters
    3,  // Retry attempts
    1   // Priority
);