<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use OwowAgency\Teams\Enums\InvitationStatus;
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
     * The belongs to many relationship to all users which joined the team.
     */
    public function users(): BelongsToMany
    {
        return $this->allUsers()->wherePivot('status', InvitationStatus::JOINED);
    }

    /**
     * The belongs to many relationship to ALL users. This also includes users
     * which declined the invitation.
     */
    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(config('teams.user_model'), Invitation::class, 'model_id')
            ->wherePivot('model_type', $this->getMorphClass())
            // Often needed for Laravel Nova.
            ->withPivot(['id', 'model_type', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all users that match the given invitation status.
     *
     * @param  array|int|\OwowAgency\Teams\Enums\InvitationStatus|string  $status
     */
    public function usersWithStatus($status): Collection
    {
        return $this->users()
            ->wherePivotIn('status', Arr::wrap($status))
            ->get();
    }

    /**
     * Add the given user to the invitable model.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     * @param  null|int|\OwowAgency\Teams\Enums\InvitationStatus  $status
     */
    public function addUser(Model|int $user, $roles = null, $permissions = null, $status = null): Invitation
    {
        $status = $status ?? $this->defaultInvitationStatus();

        $invitation = $this->invitations()
            ->firstOrCreate(
                ['user_id' => $user->id ?? $user],
                [
                    'status' => $status,
                    'accepted_at' => $status === InvitationStatus::JOINED ? now() : null,
                ],
            );

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

    /**
     * Accept the invitation for the given user of this team.
     */
    public function acceptInvitation(Model|int $user): ?Invitation
    {
        return $this->replyToInvitation($user, true);
    }

    /**
     * Decline the invitation for the given user of this team.
     */
    public function declineInvitation(Model|int $user): ?Invitation
    {
        return $this->replyToInvitation($user, false);
    }

    /**
     * Accept or decline the given invitation.
     */
    private function replyToInvitation(Model|int $user, bool $accept): ?Invitation
    {
        $invitation = $this->invitations()
            ->where('user_id', $user->id ?? $user)
            ->first();

        if ($invitation === null) {
            return null;
        }

        $method = $accept ? 'accept' : 'decline';

        return tap($invitation)->$method();
    }

    /**
     * Get the default invitation status of this team.
     */
    public function defaultInvitationStatus(): int
    {
        $privacy = $this->privacy ?? config('teams.default_privacy');

        return InvitationStatus::parseFromPrivacy($privacy);
    }
}
