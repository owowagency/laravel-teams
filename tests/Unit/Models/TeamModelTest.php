<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Tests\Support\TeamType;
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
            'type' => null,
        ]);
    }

    /** @test */
    public function it_scopes_types_with_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create([
            'type' => TeamType::DEFAULT,
        ]);

        $this->assertTrue($team->is(Team::type(TeamType::DEFAULT)->first()));
    }

    /** @test */
    public function it_scopes_types_with_array_of_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        $team = Team::factory()->create([
            'type' => TeamType::DEFAULT,
        ]);

        $this->assertTrue($team->is(Team::type([TeamType::DEFAULT])->first()));
    }

    /** @test */
    public function it_has_users(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->forModel($team)->create();

        $this->assertJsonStructureSnapshot($team->users);
    }
}
