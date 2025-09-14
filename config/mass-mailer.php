<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mass Mailer Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Mass Mailer package.
    | You can publish this file to your config directory and modify it.
    |
    */

    'enabled' => env('MASS_MAILER_ENABLED', true),

    'queue' => [
        'connection' => env('MASS_MAILER_QUEUE_CONNECTION', 'database'),
        'name' => env('MASS_MAILER_QUEUE_NAME', 'mass-mailer'),
    ],

    'batch_size' => env('MASS_MAILER_BATCH_SIZE', 50),

    'rate_limiting' => [
        'enabled' => env('MASS_MAILER_RATE_LIMITING_ENABLED', true),
        'max_per_minute' => env('MASS_MAILER_MAX_PER_MINUTE', 100),
    ],

    'attachments' => [
        'max_size' => env('MASS_MAILER_MAX_ATTACHMENT_SIZE', 1024), // KB (1MB)
        'allowed_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'],
        'storage_disk' => env('MASS_MAILER_STORAGE_DISK', 'public'),
    ],

    'templates' => [
        'enabled' => env('MASS_MAILER_TEMPLATES_ENABLED', true),
        'path' => env('MASS_MAILER_TEMPLATES_PATH', 'mass-mailer/templates'),
    ],

    'logging' => [
        'enabled' => env('MASS_MAILER_LOGGING_ENABLED', true),
        'table' => env('MASS_MAILER_LOG_TABLE', 'mass_mailer_logs'),
    ],

    'email_providers' => [
        'default' => env('MASS_MAILER_DEFAULT_PROVIDER', 'smtp'),
        'providers' => [
            'smtp' => [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'encryption' => env('MAIL_ENCRYPTION'),
            ],
            // Add other providers like SendGrid, Mailgun, etc.
        ],
    ],

/*
|--------------------------------------------------------------------------
| Multiple Senders Configuration
|--------------------------------------------------------------------------
|
| Enable multiple senders for mass mailing. If enabled, the system will use
| the configured sender profiles for sending emails.
| Set MASS_MAILER_MULTIPLE_SENDERS to true to enable.
|
*/
'multiple_senders' => env('MASS_MAILER_MULTIPLE_SENDERS', false),
'senders' => [
    [
        'name' => env('MASS_MAILER_SENDER_1_NAME', 'Sender 1'),
        'email' => env('MASS_MAILER_SENDER_1_EMAIL'),
        'host' => env('MASS_MAILER_SENDER_1_HOST'),
        'port' => env('MASS_MAILER_SENDER_1_PORT'),
        'username' => env('MASS_MAILER_SENDER_1_USERNAME'),
        'password' => env('MASS_MAILER_SENDER_1_PASSWORD'),
        'encryption' => env('MASS_MAILER_SENDER_1_ENCRYPTION'),
    ],
    // Add more sender profiles as needed, e.g., SENDER_2_*, etc.
],

/*
|--------------------------------------------------------------------------
| Sender Model Configuration
|--------------------------------------------------------------------------
|
| Specify the model class for loading senders from the database.
| This is used when multiple_senders is enabled and you want to load
| sender profiles dynamically from the database instead of static config.
| Default: Mrclln\MassMailer\Models\MassMailerSender
|
*/
'sender_model' => env('MASS_MAILER_SENDER_MODEL', 'Mrclln\MassMailer\Models\MassMailerSender'),
//'sender_model' => Auth::user()->massMailerSenders()->get()->toArray(),

'ui' => [
        'framework' => env('MASS_MAILER_UI_FRAMEWORK', 'bootstrap'), // bootstrap, tailwind
        'theme' => env('MASS_MAILER_UI_THEME', 'default'),
        'variables' => ['email', 'first_name', 'last_name'],
        'editor' => [
            'type' => env('MASS_MAILER_EDITOR_TYPE', 'quill'), // quill, tinymce, etc.
        ],
    ],
];
