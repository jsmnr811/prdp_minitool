<?php

namespace App\Observers;

use App\Models\GeomappingUser;
use App\Notifications\UserRegistered;

class GeomappingUserObserver
{
    /**
     * Handle the GeomappingUser "created" event.
     */
    public function created(GeomappingUser $geomappingUser): void
    {
        $geomappingUser->notify(new UserRegistered($geomappingUser));
    }

    /**
     * Handle the GeomappingUser "updated" event.
     */
    public function updated(GeomappingUser $geomappingUser): void
    {
        //
    }

    /**
     * Handle the GeomappingUser "deleted" event.
     */
    public function deleted(GeomappingUser $geomappingUser): void
    {
        //
    }

    /**
     * Handle the GeomappingUser "restored" event.
     */
    public function restored(GeomappingUser $geomappingUser): void
    {
        //
    }

    /**
     * Handle the GeomappingUser "force deleted" event.
     */
    public function forceDeleted(GeomappingUser $geomappingUser): void
    {
        //
    }
}
