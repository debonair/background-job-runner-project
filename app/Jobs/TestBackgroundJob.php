<?php

namespace App\Jobs;

class TestBackgroundJob
{
    public function processData($data)
    {
        // 50% chance of success/failure for testing
        if (rand(0, 1) === 0) {
            throw new \Exception("Random test error");
        }
        
        // Simulate processing time
        sleep(1);
        
        return "Successfully processed: " . json_encode($data);
    }
}