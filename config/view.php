<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    | Using storage_path() directly (without realpath()) so config:cache works
    | even when the directory is created at build time inside Docker.
    */

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),

];
