<?php

namespace OwowAgency\Teams\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use OwowAgency\Snapshots\MatchesSnapshots;
use OwowAgency\Teams\TeamsServiceProvider;
use OwowAgency\Teams\Tests\Support\Models\User;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase, MatchesSnapshots;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Setup the database.
     */
    protected function setUpDatabase(): void
    {
        // Setup Spatie permissions package.
        include_once __DIR__.'/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        $this->app[Role::class]->create(['name' => 'admin']);
        $this->app[Permission::class]->create(['name' => 'edit-users']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('teams.user_model', User::class);
        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            TeamsServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }
}
