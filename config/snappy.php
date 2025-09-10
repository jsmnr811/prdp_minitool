<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timeout:
    |
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */

    'pdf' => [
        'enabled' => true,
        'binary'  => env('SNAPPY_IMAGE_BINARY', '/usr/local/bin/wkhtmltoimage-custom'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => env('SNAPPY_PDF_BINARY', '/usr/local/bin/wkhtmltopdf-custom'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
    //  'pdf' => [
    //     'enabled' => true,
    //     // Use ENV variable if set, otherwise fallback to Windows path
    //     'binary'  => env('WKHTMLTOPDF_PATH', '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"'),
    //     'timeout' => false,
    //     'options' => [],
    //     'env'     => [],
    // ],

    // 'image' => [
    //     'enabled' => true,
    //     'binary'  => env('WKHTMLTOIMAGE_PATH', '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"'),
    //     'timeout' => false,
    //     'options' => [
    //         'format'  => 'jpg',   // Default format
    //         'width'   => 330,     // Your case needed 330
    //         'height'  => 520,     // Your case needed 520
    //         'quality' => 85,      // Quality for JPG
    //     ],
    //     'env'     => [],
    // ],

];
