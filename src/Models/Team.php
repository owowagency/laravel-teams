<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OwowAgency\Database\Factories\TeamFactory;

class Team extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name', 'type',
    ];

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
    protected static function newFactory(): Factory
    {
        return new TeamFactory();
    }
}
