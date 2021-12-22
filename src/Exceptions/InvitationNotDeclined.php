<?php

namespace OwowAgency\Teams\Exceptions;

use Exception;

class InvitationNotDeclined extends Exception
{
    /**
     * InvitationAlreadyReopened constructor.
     */
    public function __construct()
    {
        parent::__construct('The invitation has not been declined.');
    }
}
