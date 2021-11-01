<?php

namespace OwowAgency\Teams\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OwowAgency\Database\Factories\TeamFactory;
use OwowAgency\Teams\TeamType;

class Team extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        // Wait for a new tag to be released for: https://github.com/laravel/framework/pull/39315
        // 'type' => TeamType::class,
    ];

    /**
     * Scope the type.
     */
    public function scopeType(Builder $query, BackedEnum|int|string|array $type): Builder
    {
        return $query->whereIn(
            'type',
            array_map(fn (BackedEnum|int|string $type) => $type->value ?? $type, Arr::wrap($type)),
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
