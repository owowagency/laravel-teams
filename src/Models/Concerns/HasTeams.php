<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;

trait HasTeams
{
    /**
     * The has many relationship to teams.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(config('teams.model'), Invitation::class, 'user_id', 'model_id')
            ->wherePivot('model_type', (new Team())->getMorphClass())
            // Often needed for Laravel Nova.
            ->withPivot('id')
            ->withTimestamps();
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
}
