<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwowAgency\Database\Factories\TeamTeamFactory;

class TeamTeam extends Pivot
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * The belongs to relationship to the parent team.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(config('teams.models.team'));
    }

    /**
     * The belongs to relationship to the child team.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(config('teams.models.team'));
    }

    /**
     * {@inheritdoc}
     */
    protected static function newFactory()
    {
        return new TeamTeamFactory();
    }
}
