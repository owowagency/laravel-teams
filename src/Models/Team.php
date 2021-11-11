<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use OwowAgency\Database\Factories\TeamFactory;
use OwowAgency\Teams\Enums\TeamPrivacy;
use OwowAgency\Teams\Models\Concerns\InteractsWithInvitations;
use OwowAgency\Teams\Models\Concerns\RelatesToTeams;
use OwowAgency\Teams\Models\Contracts\HasInvitations;

class Team extends Model implements HasInvitations
{
    use HasFactory, InteractsWithInvitations, RelatesToTeams;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name', 'creator_id', 'type', 'privacy',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'type' => 'integer',
        'privacy' => TeamPrivacy::class,
    ];

    /**
     * The belongs to relationship to the creator of the team.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('teams.user_model'));
    }

    /**
     * Scope a query to include only teams with the given privacy.
     */
    public function scopePrivacy(Builder $query, int|array $privacy): Builder
    {
        return $query->whereIn(
            'privacy',
            array_map(fn (int $type) => $type, Arr::wrap($privacy)),
        );
    }

    /**
     * Scope a query to include only teams with the given type.
     */
    public function scopeType(Builder $query, int|array $types): Builder
    {
        return $query->whereIn(
            'type',
            array_map(fn (int $type) => $type, Arr::wrap($types)),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function newFactory()
    {
        return new TeamFactory();
    }
}
