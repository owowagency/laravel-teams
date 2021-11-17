<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwowAgency\Teams\Models\Team;

trait RelatesToTeams
{
    /**
     * The belongs to many relationship to the children.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(config('teams.models.team'), 'team_team', 'parent_id', 'child_id')
            ->using(config('teams.models.team_team'));
    }

    /**
     * The belongs to many relationship to the parents.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(config('teams.models.team'), 'team_team', 'child_id', 'parent_id')
            ->using(config('teams.models.team_team'));
    }

    /**
     * Attach the given team to either it's parent of children teams.
     */
    public function attachTeam(Team|int $team, bool $isChild = true): void
    {
        $relation = $isChild ? 'children' : 'parents';

        $this->$relation()->attach($team->id ?? $team);
    }

    /**
     * Attach the given team as parent team.
     */
    public function attachParentTeam(Team|int $team): void
    {
        $this->attachTeam($team, false);
    }

    /**
     * Attach the given team as child team.
     */
    public function attachChildTeam(Team|int $team): void
    {
        $this->attachTeam($team);
    }

    /*
     * Determine whether this team is in any way related to the given team. So
     * this method will return true if the given team is a parent OR child of
     * this model.
     */
    public function isRelatedToTeam(Team|int $team): bool
    {
        return $this->hasParentTeam($team)
            || $this->hasChildTeam($team);
    }

    /**
     * Determine whether this model has the given model as parent.
     */
    public function hasParentTeam(Team|int $team): bool
    {
        $teamsTable = (new (config('teams.models.team')))->getTable();

        return $this->parents()
            ->where("$teamsTable.id", $team->id ?? $team)
            ->exists();
    }

    /**
     * Determine whether this model has the given model as child.
     */
    public function hasChildTeam(Team|int $team): bool
    {
        $teamsTable = (new (config('teams.models.team')))->getTable();

        return $this->children()
            ->where("$teamsTable.id", $team->id ?? $team)
            ->exists();
    }
}
