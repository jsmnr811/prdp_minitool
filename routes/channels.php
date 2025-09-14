<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('commodities-updates', function ($user) {
    // Authorize users with geomapping guard
    return true;
});

Broadcast::routes(['middleware' => ['auth-geo:geomapping']]);
