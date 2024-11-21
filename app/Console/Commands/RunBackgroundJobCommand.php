<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackgroundJobRunner;

class RunBackgroundJobCommand extends Command
{
    protected $signature = 'background:job 
        {className : Fully qualified class name} 
        {method : Method to be executed} 
        {--params=* : Parameters for the method}';

    protected $description = 'Run a background job';

    protected $runner;

    public function __construct(BackgroundJobRunner $runner)
    {
        parent::__construct();
        $this->runner = $runner;
    }

    public function handle()
    {
        try {
            $result = $this->runner->run(
                $this->argument('className'),
                $this->argument('method'),
                $this->getParameters()
            );

            $this->info('Job executed successfully');
            $this->line(json_encode($result));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Job failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function getParameters(): array
    {
        $params = $this->option('params');
        if (empty($params)) {
            return [];
        }

        if (is_string($params[0])) {
            $decoded = json_decode($params[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [$decoded];
            }
        }

        return $params;
    }
}