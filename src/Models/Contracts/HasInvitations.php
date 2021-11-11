<?php

namespace OwowAgency\Teams\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Models\Invitation;

interface HasInvitations
{
    /**
     * The morph many relationship to invitations.
     */
    public function invitations(): MorphMany;

    /**
     * The belongs to many relationship to all users of the model.
     */
    public function users(): BelongsToMany;

    /**
     * Invite the given user to the invitable model.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function inviteUser(Model|int $user, $roles = null, $permissions = null): Invitation;

    /**
     * Request if the given user may join the invitable model.
     */
    public function requestToJoin(Model|int $user): Invitation;

    /**
     * Add the given user to the invitable model and automatically accept the
     * invitation.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function addUser(
        Model|int $user,
        InvitationType|int $invitationType,
        $roles = null,
        $permissions = null,
        bool $autoAccept = false,
    ): Invitation;

    /**
     * Determine whether the given user is in the invitable model.
     */
    public function hasUser(Model|int $user): bool;

    /**
     * Determine whether the given user is in the invitable model and has the
     * given role.
     *
     * @param  array|\Illuminate\Support\Collection|int|\Spatie\Permission\Contracts\Role|string  $roles
     */
    public function hasUserWithRole(Model|int $user, $roles): bool;

    /**
     * Determine whether the given user is in the invitable model and has the
     * given permission to.
     *
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function hasUserWithPermissionTo(Model|int $user, $permissions): bool;

    /**
     * Determine whether the given user is invited to the model.
     */
    public function hasInvitedUser(Model|int $user): bool;

    /**
     * Remove the given user from the invitable model.
     */
    public function removeUser(Model|int $user): ?bool;

    /**
     * Accept the invitation for the given user of this team.
     */
    public function acceptInvitation(Model|int $user): ?Invitation;

    /**
     * Decline the invitation for the given user of this team.
     */
    public function declineInvitation(Model|int $user): ?Invitation;
}
