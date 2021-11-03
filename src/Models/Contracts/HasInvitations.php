<?php

namespace OwowAgency\Teams\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwowAgency\Teams\Models\Invitation;

interface HasInvitations
{
    /**
     * The morph many relationship to invitations.
     */
    public function invitations(): MorphMany;

    /**
     * The belongs to many relationship to users.
     */
    public function users(): BelongsToMany;

    /**
     * Add the given user to the invitable model.
     */
    public function addUser(Model|int $user): Invitation;

    /**
     * Remove the given user from the invitable model.
     */
    public function removeUser(Model|int $user): int;

    /**
     * Get the invitation for the given user.
     */
    public function getInvitation(Model|int $user): ?Invitation;
}
