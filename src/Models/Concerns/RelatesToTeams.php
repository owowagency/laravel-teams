<?php

namespace OwowAgency\Teams\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Models\TeamTeam;

trait RelatesToTeams
{
    /**
     * The belongs to many relationship to the children.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_team', 'parent_id', 'child_id')
            ->using(TeamTeam::class);
    }

    /**
     * The belongs to many relationship to the parents.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_team', 'child_id', 'parent_id')
            ->using(TeamTeam::class);
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
        $teamsTable = (new (config('teams.model')))->getTable();

        return $this->parents()
            ->where("$teamsTable.id", $team->id ?? $team)
            ->exists();
    }

    /**
     * Determine whether this model has the given model as child.
     */
    public function hasChildTeam(Team|int $team): bool
    {
        $teamsTable = (new (config('teams.model')))->getTable();

        return $this->children()
            ->where("$teamsTable.id", $team->id ?? $team)
            ->exists();
    }
}