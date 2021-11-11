<?php

namespace OwowAgency\Teams\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwowAgency\Teams\Models\Concerns\HasTeams;
use OwowAgency\Teams\Tests\Support\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory, HasTeams;

    /**
     * {@inheritdoc}
     */
    protected static function newFactory()
    {
        return new UserFactory();
    }
}
