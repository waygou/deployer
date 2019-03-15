<?php

use Waygou\Deployer\Deployer;

return [

    'type' => env('DEPLOYER_TYPE', 'local'),

    /*
     * Your base remote server URL for the deployer api calls.
     * E.g.: http://www.johnsmith.com
     * E.g.: https://www.clickandtry.com
     */
    'remote' => [
        'url' => env('DEPLOYER_REMOTE_URL'),
        // Your route prefix, default is <your-server-url>/deployer.
        'prefix' => '/deployer',
    ],

    /*
     * What scripts/processes do you want to run before and after your
     * deployment was completed?
     * You can use a full string E.g.: 'php artisan route:list'
     * You can use an invokable Class that should return a string to run.
     */
    'scripts' => [
        'before_deployment' => [
            'php artisan route:list',
            //InvokableClass::script
        ],
        'after_deployment' => [
            'php artisan route:list',
        ],
    ],

    // What's the codebase you want to upload to your remote server?
    'codebase' => [
        'folders' => ['App', 'packages/waygou/deployer'],
        'files' => ['database/factories/UserFactory.php', 'resources/js/app.js'],
    ],

    // Storage to put the zip files that are created.
    'storage' => [
        'path' => storage_path('app/deployer'),
    ],

    /*
     * OAuth information + remote<->local token information.
     */
    'oauth' => [
        'client' => env('DEPLOYER_OAUTH_CLIENT'),
        'secret' => env('DEPLOYER_OAUTH_SECRET'),
    ],

    /*
     * Local / Remote token. Must be the same in both environments.
     */
    'token' => env('DEPLOYER_TOKEN'),
];
