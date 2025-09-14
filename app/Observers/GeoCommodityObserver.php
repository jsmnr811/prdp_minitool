<?php

namespace App\Observers;

use App\Models\GeoCommodity;
use App\Events\GeoCommodityUpdated;

class GeoCommodityObserver
{
    /**
     * Handle the GeoCommodity "created" event.
     */
    public function created(GeoCommodity $geoMappingCommodity): void
    {
        GeoCommodityUpdated::dispatch();
    }


    /**
     * Handle the GeoCommodity "deleted" event.
     */
    public function deleted(GeoCommodity $geoMappingCommodity): void
    {
        GeoCommodityUpdated::dispatch();
    }
}
