<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default namespace structure for generated
    | HTTP client classes. You can customize these values to match your
    | application's namespace conventions.
    |
    */
    'namespace' => [
        'base' => env('HTTP_CLIENT_GENERATOR_NAMESPACE', 'App\\Http\\Clients'),
        'attributes' => 'Attributes',
        'requests' => 'Requests',
        'responses' => 'Responses',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Path Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default file system paths where generated
    | HTTP client classes and tests will be created.
    |
    */
    'paths' => [
        'base' => env('HTTP_CLIENT_GENERATOR_PATH', 'app/Http/Clients'),
        'tests' => env('HTTP_CLIENT_GENERATOR_TESTS_PATH', 'tests/Unit/Http/Clients'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Configuration
    |--------------------------------------------------------------------------
    |
    | You can specify a custom path for stub files if you want to override
    | the default templates used for generating classes.
    |
    */
    'stubs' => [
        'custom_path' => env('HTTP_CLIENT_GENERATOR_STUBS_PATH', null),
    ],
];
