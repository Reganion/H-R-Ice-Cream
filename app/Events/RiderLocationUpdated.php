<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Driver;

class RiderLocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver;

    /**
     * Create a new event instance.
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    public function broadcastOn(): Channel
    {
        return new Channel('rider-location');
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver->id,
            'lat' => $this->driver->current_lat,
            'lng' => $this->driver->current_lng,
            'last_updated' => $this->driver->last_updated,
        ];
    }

}
