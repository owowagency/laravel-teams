<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\TeamType;
use OwowAgency\Teams\Tests\TestCase;

class TeamModelTest extends TestCase
{
    /** @test */
    public function it_has_a_name(): void
    {
        $team = Team::factory()->create();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => $team->name,
            'type' => TeamType::DEFAULT->value,
        ]);
    }

    /** @test */
    public function it_scopes_types(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create();

        $this->assertTrue($team->is(Team::type(TeamType::DEFAULT)->first()));
    }

    /** @test */
    public function it_scopes_types_with_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create();

        $this->assertTrue($team->is(Team::type(TeamType::DEFAULT->value)->first()));
    }

    /** @test */
    public function it_scopes_types_with_array_of_enum(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create();

        $this->assertTrue($team->is(Team::type([TeamType::DEFAULT])->first()));
    }

    /** @test */
    public function it_scopes_types_with_array_of_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create();

        $this->assertTrue($team->is(Team::type([TeamType::DEFAULT->value])->first()));
    }
}
