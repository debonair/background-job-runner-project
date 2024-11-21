# Laravel Background Job Runner

A simple, customizable background job runner for Laravel applications that works independently of Laravel's built-in queue system.

## 📋 Features

- Execute PHP classes as background jobs
- Independent from Laravel's queue system
- Built-in logging and error handling
- Simple interface for running background jobs
- Command-line interface support
- Retry mechanism for failed jobs
- Job execution priority support

## 🚀 Requirements

- PHP ^8.2
- Laravel ^11.31
- Composer

## 📥 Installation

1. Create the necessary directories:
```bash
mkdir -p app/Helpers
mkdir -p app/Services
mkdir -p app/Jobs
```

2. Add the helper file autoload to your `composer.json`:
```json
{
    "autoload": {
        "files": [
            "app/Helpers/background_job_helpers.php"
        ]
    }
}
```

3. Create the required files and directories:
```bash
# Create helper file
touch app/Helpers/background_job_helpers.php

# Create service file
touch app/Services/BackgroundJobRunner.php

# Create test job
touch app/Jobs/TestBackgroundJob.php
```

4. Publish the configuration:
```bash
php artisan vendor:publish --tag=background-job-runner
```

## 🔧 Configuration

1. Configure logging channels in `config/logging.php`:
```php
'channels' => [
    'background_jobs' => [
        'driver' => 'daily',
        'path' => storage_path('logs/background_jobs.log'),
        'level' => 'debug',
        'days' => 14,
    ],
    'background_jobs_errors' => [
        'driver' => 'daily',
        'path' => storage_path('logs/background_jobs_errors.log'),
        'level' => 'error',
        'days' => 14,
    ],
],
```

## 📖 Usage

### Creating a Background Job

Create a new job class in the `app/Jobs` directory:

```php
<?php

namespace App\Jobs;

class MyBackgroundJob
{
    public function processData(array $data): string
    {
        // Your job logic here
        return "Processed data: " . json_encode($data);
    }
}
```

### Running a Job

#### Using Helper Function

```php
$result = runBackgroundJob(
    MyBackgroundJob::class,
    'processData',
    [['key' => 'value']],  // Parameters
    3,                     // Number of retries
    1                      // Priority
);
```

#### Using Artisan Command

```bash
php artisan background:job "App\Jobs\MyBackgroundJob" "processData" --params='{"key":"value"}'
```

## 📊 Logging

Jobs are automatically logged to:
- `storage/logs/background_jobs.log` - All job executions
- `storage/logs/background_jobs_errors.log` - Error logs

## 🛠️ Advanced Usage

### Custom Job Classes

```php
<?php

namespace App\Jobs;

class CustomJob
{
    public function handle(array $data)
    {
        // Complex processing logic
    }

    public function processWithRetry($input)
    {
        // Processing with built-in retry mechanism
    }
}
```

### Error Handling

The system automatically catches and logs exceptions. You can extend this by:

```php
try {
    $result = runBackgroundJob(CustomJob::class, 'handle', [$data]);
} catch (\Exception $e) {
    // Custom error handling
}
```

## 🔍 Monitoring

You can monitor job execution through the log files:

```bash
# View recent job logs
tail -f storage/logs/background_jobs.log

# View error logs
tail -f storage/logs/background_jobs_errors.log
```

## ⚙️ Customization

### Modifying Retry Logic

You can customize the retry behavior by extending the `BackgroundJobRunner` class:

```php
<?php

namespace App\Services;

class CustomBackgroundJobRunner extends BackgroundJobRunner
{
    protected function handleRetry($jobId, $exception, $retriesLeft)
    {
        // Custom retry logic
    }
}
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 🔗 Links

- [Laravel Documentation](https://laravel.com/docs)
- [PHP Documentation](https://www.php.net/docs.php)

## 🐛 Troubleshooting

### Common Issues

1. **Class Not Found**
   ```bash
   composer dump-autoload
   ```

2. **Permission Issues**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

3. **Logging Issues**
   ```bash
   php artisan cache:clear
   ```

### Debug Mode

Enable debug mode in your `.env` file:
```
APP_DEBUG=true
```

## ✨ Credits

This package was created with love by Duma Mtungwa and inspired by the Laravel community.