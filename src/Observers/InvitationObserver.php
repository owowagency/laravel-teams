<?php

namespace OwowAgency\Teams\Observers;

use OwowAgency\Teams\Enums\InvitationStatus;
use OwowAgency\Teams\Events\UserAddedToTeam;
use OwowAgency\Teams\Events\UserDeclinedToJoinTeam;
use OwowAgency\Teams\Events\UserInvitedToTeam;
use OwowAgency\Teams\Events\UserJoinedTeam;
use OwowAgency\Teams\Events\UserLeftTeam;
use OwowAgency\Teams\Events\UserRequestedToJoinTeam;
use OwowAgency\Teams\Models\Invitation;

class InvitationObserver
{
    /**
     * Handle the Invitation "created" event.
     */
    public function created(Invitation $invitation): void
    {
        UserAddedToTeam::dispatch($invitation);

        $this->dispatchStatusEvent($invitation);
    }

    /**
     * Handle the Invitation "updated" event.
     */
    public function updated(Invitation $invitation): void
    {
        if ($invitation->wasChanged('status')) {
            $this->dispatchStatusEvent($invitation);
        }
    }

    /**
     * Handle the Invitation "deleted" event.
     */
    public function deleted(Invitation $invitation): void
    {
        UserLeftTeam::dispatch($invitation);
    }

    /*
     * Dispatch the status event of the given invitation.
     */
    private function dispatchStatusEvent(Invitation $invitation): void
    {
        $event = match ($invitation->status->value) {
            InvitationStatus::JOINED => UserJoinedTeam::class,
            InvitationStatus::INVITED => UserInvitedToTeam::class,
            InvitationStatus::REQUESTED_TO_JOIN => UserRequestedToJoinTeam::class,
            InvitationStatus::DECLINED => UserDeclinedToJoinTeam::class,
        };

        $event::dispatch($invitation);
    }
}
