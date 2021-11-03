<?php

namespace OwowAgency\Teams\Enums;

use BenSampo\Enum\Enum;

final class InvitationStatus extends Enum
{
    const JOINED = 0;

    const INVITED = 1;

    const REQUESTED_TO_JOIN = 2;

    const DECLINED = 3;

    /**
     * Parse the invitation status from the given privacy.
     */
    public static function parseFromPrivacy(int|TeamPrivacy $privacy): int
    {
        return match ($privacy->value ?? $privacy) {
            TeamPrivacy::OPEN => InvitationStatus::JOINED,
            TeamPrivacy::INVITE_ONLY => InvitationStatus::INVITED,
            TeamPrivacy::REQUESTABLE => InvitationStatus::REQUESTED_TO_JOIN,
        };
    }
}
