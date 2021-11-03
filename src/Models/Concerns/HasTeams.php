<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;

trait HasTeams
{
    /**
     * The has many relationship to teams.
     */
    public function teams(): BelongsToMany
    {
        $teamModel = config('teams.model');

        return $this->belongsToMany($teamModel, Invitation::class, 'user_id', 'model_id')
            ->wherePivot('model_type', (new $teamModel())->getMorphClass())
            // Often needed for Laravel Nova.
            ->withPivot(['id', 'model_type', 'status'])
            ->withTimestamps();
    }

    /**
     * The has many relationship to the user's invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Determine whether the user belongs to the given team (id).
     */
    public function belongsToTeam(Team|int $team): bool
    {
        $table = $this->teams()->getRelated()->getTable();

        return $this->teams()
            ->where("$table.id", $team->id ?? $team)
            ->exists();
    }

    /**
     * Determine whether the user belongs to the given team (id) and has the
     * given role(s).
     */
    public function hasTeamRole(Team|int $team, $roles): bool
    {
        $table = $this->invitations()->getRelated()->getTable();

        return $this->invitations()
            ->where("$table.model_id", $team->id ?? $team)
            ->where('model_type', (new (config('teams.model')))->getMorphClass())
            ->role($roles)
            ->exists();
    }

    /**
     * Determine whether the user belongs to the given team (id) and has the
     * given permission(s).
     */
    public function hasTeamPermissionTo(Team|int $team, $permissions): bool
    {
        $table = $this->invitations()->getRelated()->getTable();

        return $this->invitations()
            ->where("$table.model_id", $team->id ?? $team)
            ->where('model_type', (new (config('teams.model')))->getMorphClass())
            ->permission($permissions)
            ->exists();
    }
}
