<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Exception;

class BackgroundJobController extends Controller
{
    private function processJobLogs($path): array
    {
        if (!File::exists($path)) return [];
        $content = File::get($path);
        preg_match_all('/\{(?:[^{}]|(?R))*\}/', $content, $matches);
        
        return collect($matches[0])->map(function($json) {
            $data = json_decode($json, true);
            return isset($data['job_id']) ? [
                'job_id' => $data['job_id'],
                'class' => $data['class'] ?? null,
                'method' => $data['method'] ?? null,
                'status' => $data['status'] ?? "failed",
                'timestamp' => $data['timestamp'] ?? null
            ] : null;
        })->filter()->values()->all();
    }

    function processErrorLogs($logLines): Array {
        $parsedErrors = [];
    
        foreach ($logLines as $line) {
            // Extract the JSON part using a regular expression
            preg_match('/\{.*\}/', $line, $matches);
    
            // Parse the JSON data
            $jsonData = json_decode($matches[0], true);
    
            if ($jsonData !== null) {
                $parsedErrors[] = $jsonData;
            } else {
                echo "Error parsing JSON: $matches[0]\n";
            }
        }
    
        return $parsedErrors;
    }

    public function data(): JsonResponse
    {
        try {
            $today = Carbon::now()->format('Y-m-d');
            $jobsLog = storage_path("logs/background_jobs-{$today}.log");
            $errorsLog = storage_path("logs/background_jobs_errors-{$today}.log");

            $jobs = $this->processJobLogs($jobsLog);
            $errors = $this->processJobLogs($errorsLog);
            

            return response()->json([
                'jobs' => $jobs,
                'errors' => $errors
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Log files not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function index()
    {
        return view('background-jobs.dashboard');
    }
}