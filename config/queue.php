<?php

return [
    'default' => env('QUEUE_CONNECTION', 'database'),
    'connections' => [
        'sync'     => ['driver' => 'sync'],
        'database' => ['driver' => 'database', 'connection' => null,
                       'table' => 'jobs', 'queue' => 'default', 'retry_after' => 90, 'after_commit' => false],
        'redis'    => ['driver' => 'redis', 'connection' => 'default',
                       'queue' => env('REDIS_QUEUE', 'default'), 'retry_after' => 90, 'block_for' => null],
    ],
    'batching'    => ['database' => env('DB_CONNECTION', 'mysql'), 'table' => 'job_batches'],
    'failed'      => ['driver' => 'database-uuids', 'database' => env('DB_CONNECTION', 'mysql'), 'table' => 'failed_jobs'],
];
