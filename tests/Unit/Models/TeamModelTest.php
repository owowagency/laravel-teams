<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

use OwowAgency\Teams\Models\Team;
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
        ]);
    }
}
