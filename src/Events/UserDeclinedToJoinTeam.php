<?php

namespace OwowAgency\Teams\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OwowAgency\Teams\Models\Invitation;

class UserDeclinedToJoinTeam
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * UserAddedToTeam constructor.
     */
    public function __construct(public Invitation $invitation)
    {
        //
    }
}
