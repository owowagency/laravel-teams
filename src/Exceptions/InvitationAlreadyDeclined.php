<?php

namespace OwowAgency\Teams\Exceptions;

use Exception;

class InvitationAlreadyDeclined extends Exception
{
    /**
     * InvitationAlreadyDeclined constructor.
     */
    public function __construct()
    {
        parent::__construct('Invitation is already declined.');
    }
}
