<?php

namespace OwowAgency\Teams\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected static function newFactory(): Factory
    {
        return new UserFactory();
    }
}
