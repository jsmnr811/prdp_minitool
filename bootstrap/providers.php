<?php

use Yajra\DataTables\DataTablesServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    Barryvdh\Debugbar\ServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
    SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
    Yajra\DataTables\DataTablesServiceProvider::class,
];
