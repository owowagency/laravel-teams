<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwowAgency\Database\Factories\TeamFactory;

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
    protected static function newFactory(): Factory
    {
        return new TeamFactory();
    }
}
