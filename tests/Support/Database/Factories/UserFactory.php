<?php

namespace OwowAgency\Teams\Tests\Support\Database\Factories;

use Orchestra\Testbench\Factories\UserFactory as BaseUserFactory;
use OwowAgency\Teams\Tests\Support\Models\User;

class UserFactory extends BaseUserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;
}
