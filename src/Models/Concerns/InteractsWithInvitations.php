<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwowAgency\Teams\Models\Invitation;

trait InteractsWithInvitations
{
    /**
     * The morph many relationship to invitations.
     */
    public function invitations(): MorphMany
    {
        return $this->morphMany(Invitation::class, 'model');
    }

    /**
     * The belongs to many relationship to users.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('teams.user_model'), Invitation::class, 'model_id')
            ->wherePivot('model_type', $this->getMorphClass())
            // Often needed for Laravel Nova.
            ->withPivot('id')
            ->withTimestamps();
    }

    /**
     * Add the given user to the invitable model.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function addUser(Model|int $user, $roles = null, $permissions = null): Invitation
    {
        $invitation = $this->invitations()->firstOrCreate([
            'user_id' => $user->id ?? $user,
        ]);

        if (! empty($roles)) {
            $invitation->assignRole($roles);
        }

        if (! empty($permissions)) {
            $invitation->givePermissionTo($permissions);
        }

        return $invitation;
    }

    /**
     * Determine whether the given user is in the invitable model.
     */
    public function hasUser(Model|int $user): bool
    {
        return $this->hasUserQuery($user)->exists();
    }

    /**
     * Determine whether the given user belongs to the invitable model and has
     * the correct role.
     */
    public function hasUserWithRole(Model|int $user, $roles): bool
    {
        return $this->hasUserQuery($user)
            ->role($roles)
            ->exists();
    }

    /**
     * Determine whether the given user is in the invitable model and has the
     * given permission to.
     */
    public function hasUserWithPermissionTo(Model|int $user, $permissions): bool
    {
        return $this->hasUserQuery($user)
            ->permission($permissions)
            ->exists();
    }

    /*
     * Build the base of the "hasUser" query.
     */
    private function hasUserQuery(Model|int $user): MorphMany
    {
        $table = $this->invitations()->getRelated()->getTable();

        return $this->invitations()
            ->where("$table.user_id", $user->id ?? $user);
    }

    /**
     * Remove the given user from the invitable model.
     */
    public function removeUser(Model|int $user): ?bool
    {
        return $this->invitations()
            ->where('user_id', $user->id ?? $user)
            // First get the invitation before deleting it. Doing so, we'll make
            // sure that the model events will be fired. Resulting in the roles
            // and permissions of the model to be deleted.
            ->first()
            ?->delete();
    }
}
