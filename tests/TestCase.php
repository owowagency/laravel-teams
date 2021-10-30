<?php

namespace OwowAgency\Teams\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use OwowAgency\Teams\TeamsServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            TeamsServiceProvider::class,
        ];
    }
}
