<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

use OwowAgency\Teams\Enums\TeamPrivacy;
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

        Team::factory()->create([
            'type' => TeamType::DEFAULT,
        ]);

        $this->assertJsonStructureSnapshot(Team::type(TeamType::DEFAULT)->get());
    }

    /** @test */
    public function it_scopes_types_with_array_of_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'type' => 99,
        ]);

        Team::factory()->create([
            'type' => TeamType::DEFAULT,
        ]);

        $this->assertJsonStructureSnapshot(Team::type([TeamType::DEFAULT])->get());
    }

    /** @test */
    public function it_scopes_privacy_with_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'privacy' => TeamPrivacy::REQUESTABLE,
        ]);

        Team::factory()->create([
            'privacy' => TeamPrivacy::OPEN,
        ]);

        $this->assertJsonStructureSnapshot(Team::privacy(TeamPrivacy::OPEN)->get());
    }

    /** @test */
    public function it_scopes_privacy_with_array_of_integers(): void
    {
        // Create team with different type.
        Team::factory()->create([
            'privacy' => TeamPrivacy::REQUESTABLE,
        ]);

        Team::factory()->create([
            'privacy' => TeamPrivacy::OPEN,
        ]);

        $this->assertJsonStructureSnapshot(Team::privacy([TeamPrivacy::OPEN])->get());
    }

    /** @test */
    public function it_has_users(): void
    {
        $team = Team::factory()->create();

        Invitation::factory()->forModel($team)->accepted()->create();

        $this->assertJsonStructureSnapshot($team->users);
    }
}
