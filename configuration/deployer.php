<?php

use Waygou\Deployer\Deployer;

return [

    /**
     * Environment type:
     * 'local' = Your local dev machine
     * 'remote' = Your remote codebase server.
     */
    'type' => env('DEPLOYER_TYPE', 'local'),

    /*
     * Your remove server information group.
     */
    'remote' => [
        // Your remote server URL. Configured in your .env when installing deployer on your local server.
        'url' => env('DEPLOYER_REMOTE_URL'),

        // Your route prefix, default is <your-server-url>/deployer.
        'prefix' => '/deployer',
    ],

    /*
     * What scripts/processes do you want to run before and after your
     * deployment was completed?
     * You can use (as many as you want):
     * Artisan commands. E.g.: 'cache:clear'
     * Invokable Classes: E.g.: MyClass::class (will call your __invoke() directly).
     * Custom Class methods: E.g.: 'MyClass@myMethod'.
     */
    'scripts' => [
        'before_deployment' => [
            'route:list', // Just an example.
        ],
        'after_deployment' => [
            'cache:clear',
            'view:clear',
            'clear'
        ],
    ],

    // What's the codebase you want to upload to your remote server?
    'codebase' => [
        'folders' => [
            'app'
        ],
        'files' => [
            'database/factories/UserFactory.php',
            'resources/js/app.js'
        ],
        // Files or folders where you don't want to override them on your remote server.
        'whitelist' => [
            '.env'
        ]
    ],

    // Storage where it should be created all the transaction folders for your codebase.
    'storage' => [
        'path' => storage_path('app/deployer'),
    ],

    /*
     * OAuth information + remote<->local token information.
     * Automatically filled on the remote server installation.
     */
    'oauth' => [
        'client' => env('DEPLOYER_OAUTH_CLIENT'),
        'secret' => env('DEPLOYER_OAUTH_SECRET'),
    ],

    /*
     * Local / Remote token. Must be the same in both environments.
     * Automatically created on your local or remote server installations.
     */
    'token' => env('DEPLOYER_TOKEN'),
];
