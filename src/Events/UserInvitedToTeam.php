<?php

namespace OwowAgency\Teams\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OwowAgency\Teams\Models\Invitation;

class UserInvitedToTeam
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * UserInvitedToTeam constructor.
     */
    public function __construct(public Invitation $invitation)
    {
        //
    }
}
