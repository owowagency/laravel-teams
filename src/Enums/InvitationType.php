<?php

namespace OwowAgency\Teams\Enums;

use BenSampo\Enum\Enum;

final class InvitationType extends Enum
{
    /**
     * The user has requested to join the team.
     */
    const REQUEST = 0;

    /**
     * The user has been invited to join the team.
     */
    const INVITATION = 1;
}
