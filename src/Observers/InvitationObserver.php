<?php

namespace OwowAgency\Teams\Observers;

use OwowAgency\Teams\Enums\InvitationType;
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
        $event = match ($invitation->type->value) {
            InvitationType::REQUEST => UserRequestedToJoinTeam::class,
            InvitationType::INVITATION => UserInvitedToTeam::class,
        };

        $event::dispatch($invitation);
    }

    /**
     * Handle the Invitation "updated" event.
     */
    public function updated(Invitation $invitation): void
    {
        if (
            $invitation->wasChanged('accepted_at')
            && $invitation->accepted_at !== null
        ) {
            UserJoinedTeam::dispatch($invitation);
        }

        if (
            $invitation->wasChanged('declined_at')
            && $invitation->declined_at !== null
        ) {
            UserDeclinedToJoinTeam::dispatch($invitation);
        }
    }

    /**
     * Handle the Invitation "deleted" event.
     */
    public function deleted(Invitation $invitation): void
    {
        if ($invitation->accepted_at !== null) {
            UserLeftTeam::dispatch($invitation);
        }
    }
}
