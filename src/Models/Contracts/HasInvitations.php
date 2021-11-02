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
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function addUser(Model|int $user, $roles = [], $permissions = []): Invitation;

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
}
