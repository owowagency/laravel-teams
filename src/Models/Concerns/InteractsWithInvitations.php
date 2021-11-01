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
            ->withTimestamps();
    }

    /**
     * Add the given user to the invitable model.
     */
    public function addUser(Model|int $user): Invitation
    {
        return $this->invitations()->firstOrCreate([
            'user_id' => $user->id ?? $user,
        ]);
    }

    /**
     * Remove the given user from the invitable model.
     */
    public function removeUser(Model|int $user): int
    {
        return $this->invitations()
            ->where('user_id', $user->id ?? $user)
            ->delete();
    }
}
