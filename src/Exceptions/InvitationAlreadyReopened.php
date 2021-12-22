<?php

namespace OwowAgency\Teams\Exceptions;

use Exception;

class InvitationAlreadyReopened extends Exception
{
    /**
     * InvitationAlreadyReopened constructor.
     */
    public function __construct()
    {
        parent::__construct('Invitation is already reopened.');
    }
}
