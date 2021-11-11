<?php

namespace OwowAgency\Teams\Exceptions;

use Exception;

class InvitationAlreadyAccepted extends Exception
{
    /**
     * InvitationAlreadyExcepted constructor.
     */
    public function __construct()
    {
        parent::__construct('Invitation is already accepted.');
    }
}
