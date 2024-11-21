<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\BackgroundJobController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/background-jobs', [BackgroundJobController::class, 'index'])
    ->name('background-jobs.dashboard');

Route::get('/api/background-jobs', [BackgroundJobController::class, 'data'])
    ->name('background-jobs.data');

Route::get('/debug-logs', function() {
    $today = now()->format('Y-m-d');
    $jobsLog = storage_path("logs/background_jobs-{$today}.log");
    $errorsLog = storage_path("logs/background_jobs_errors-{$today}.log");
    
    return [
        'jobs_log' => File::exists($jobsLog) ? File::get($jobsLog) : 'No jobs log file',
        'errors_log' => File::exists($errorsLog) ? File::get($errorsLog) : 'No errors log file',
        'date' => $today,
        'jobs_path' => $jobsLog,
        'errors_path' => $errorsLog
    ];
});