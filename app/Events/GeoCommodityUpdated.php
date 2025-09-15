<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class GeoCommodityUpdated implements ShouldBroadcastNow
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message ="Message Sent")
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [new Channel('commodities-updates')];
    }

    public function broadcastAs(): string
    {
        return 'geo.commodity.updated';
    }
}
