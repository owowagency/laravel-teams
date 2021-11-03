<?php

namespace OwowAgency\Teams\Enums;

use BenSampo\Enum\Enum;

final class TeamPrivacy extends Enum
{
    const OPEN = 0;

    const INVITE_ONLY = 1;

    const REQUESTABLE = 2;
}
