<?php

namespace OwowAgency\Teams\Models\Contracts;

use Illuminate\Database\Eloquent\Collection;
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
     * The belongs to many relationship to all users which joined the team.
     */
    public function users(): BelongsToMany;

    /**
     * The belongs to many relationship to ALL users. This also includes users
     * which declined the invitation.
     */
    public function allUsers(): BelongsToMany;

    /**
     * Get all users that match the given invitation status.
     *
     * @param  array|int|\OwowAgency\Teams\Enums\InvitationStatus|string  $status
     */
    public function usersWithStatus($status): Collection;

    /**
     * Add the given user to the invitable model.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     * @param  null|int|\OwowAgency\Teams\Enums\InvitationStatus  $status
     */
    public function addUser(Model|int $user, $roles = null, $permissions = null, $status = null): Invitation;

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
