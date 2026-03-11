<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderLocationUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $riderId;
    public $lat;
    public $lng;

    /**
     * Create a new event instance.
     */
    public function __construct($riderId, $lat, $lng)
    {
        $this->riderId = $riderId;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    public function broadcastOn()
    {
        return new PrivateChannel('rider.' . $this->riderId);
    }
}
