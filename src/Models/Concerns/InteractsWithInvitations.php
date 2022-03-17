<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Query\Builder;

trait InteractsWithInvitations
{
    /**
     * The morph many relationship to invitations.
     */
    public function invitations(): MorphMany
    {
        return $this->morphMany(config('teams.models.invitation'), 'model');
    }

    /**
     * The belongs to many relationship to all users of the model.
     */
    public function users(): BelongsToMany
    {
        $instance = new (config('teams.user_model'));
        $invitationModel = config('teams.models.invitation');

        // Use a custom belongs to many relationship so that we can easily
        // add scopes on the pivot relationship.
        $belongsToMany = new BelongsToMany(
            $this->newTeamsQuery($instance), $this, (new $invitationModel())->getTable(), 'model_id',
            $instance->getForeignKey(), $this->getKeyName(), $instance->getKeyName(),
        );

        return $belongsToMany->using($invitationModel)
            ->withAccepted()
            ->withPivot(['id', 'model_type', 'type', 'accepted_at', 'declined_at'])
            ->withTimestamps();
    }

    /**
     * Create a new teas Eloquent query builder for the model.
     */
    public function newTeamsQuery(Model $model): EloquentBuilder
    {
        $connection = $model->getConnection();

        $query = $model->newEloquentBuilder(
            new Builder(
                $connection, $connection->getQueryGrammar(), $connection->getPostProcessor(),
            ),
        );

        return $query->setModel($model);
    }

    /**
     * Invite the given user to the invitable model.
     *
     * @param  array|int|\Spatie\Permission\Contracts\Role|string  $roles
     * @param  array|\Illuminate\Support\Collection|\Spatie\Permission\Contracts\Permission|string  $permissions
     */
    public function inviteUser(Model|int $user, $roles = null, $permissions = null): Invitation
    {
        return $this->addUser($user, InvitationType::INVITATION, $roles, $permissions);
    }

    /**
     * Request if the given user may join the invitable model.
     */
    public function requestToJoin(Model|int $user): Invitation
    {
        return $this->addUser($user, InvitationType::REQUEST);
    }

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
    ): Invitation {
        $invitation = $this->invitations()
            // If the invitation already exists we'll retrieve it.
            ->firstOrCreate(
                ['user_id' => $user->id ?? $user],
                ['type' => $invitationType->value ?? $invitationType],
            );

        if (! empty($roles)) {
            $invitation->assignRole($roles);
        }

        if (! empty($permissions)) {
            $invitation->givePermissionTo($permissions);
        }

        try {
            if ($autoAccept) {
                $invitation->accept();
            }
        } catch (InvitationAlreadyAccepted) {
            //
        } finally {
            // Refresh to model to make sure all properties are properly retrieved.
            return $invitation->refresh();
        }
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
            ->accepted()
            ->where("$table.user_id", $user->id ?? $user);
    }

    /**
     * Determine whether the given user is in the invitable model.
     */
    public function hasInvitedUser(Model|int $user): bool
    {
        $table = $this->invitations()->getRelated()->getTable();

        return $this->invitations()
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where("$table.user_id", $user->id ?? $user)
            ->exists();
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
     * Get the invitation for the given user.
     */
    public function getInvitation(Model|int $user): ?Invitation
    {
        return $this->invitations()
            ->where('user_id', $user->id ?? $user)
            ->first();
    }
}
