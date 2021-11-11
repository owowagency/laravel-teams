<?php

namespace OwowAgency\Teams\Enums;

use BenSampo\Enum\Enum;

final class TeamPrivacy extends Enum
{
    /**
     * The team is open and everybody can join.
     */
    const OPEN = 0;

    /**
     * User can only be invited to the team.
     */
    const INVITE_ONLY = 1;

    /**
     * User can request to join the team.
     */
    const REQUESTABLE = 2;
}
