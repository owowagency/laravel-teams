<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Concerns;

use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Models\TeamTeam;
use OwowAgency\Teams\Tests\TestCase;

class RelatesToTeamsTest extends TestCase
{
    /** @test */
    public function it_has_parent_teams(): void
    {
        $teamTeam = TeamTeam::factory()->create();

        $this->assertJsonStructureSnapshot($teamTeam->child->parents);
    }

    /** @test */
    public function it_has_children_teams(): void
    {
        $teamTeam = TeamTeam::factory()->create();

        $this->assertJsonStructureSnapshot($teamTeam->parent->children);
    }

    /** @test */
    public function it_attaches_teams_as_parent_using_models(): void
    {
        $teams = Team::factory(2)->create();

        $teams[0]->attachTeam($teams[1], false);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[0]->id,
            'parent_id' => $teams[1]->id,
        ]);

        $teams = Team::factory(2)->create();

        $teams[0]->attachParentTeam($teams[1]);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[0]->id,
            'parent_id' => $teams[1]->id,
        ]);
    }

    /** @test */
    public function it_attaches_teams_as_parent_using_ids(): void
    {
        $teams = Team::factory(2)->create();

        $teams[0]->attachTeam($teams[1]->id, false);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[0]->id,
            'parent_id' => $teams[1]->id,
        ]);

        $teams = Team::factory(2)->create();

        $teams[0]->attachParentTeam($teams[1]->id);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[0]->id,
            'parent_id' => $teams[1]->id,
        ]);
    }

    /** @test */
    public function it_attaches_teams_as_child_using_models(): void
    {
        $teams = Team::factory(2)->create();

        $teams[0]->attachTeam($teams[1]);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[1]->id,
            'parent_id' => $teams[0]->id,
        ]);

        $teams = Team::factory(2)->create();

        $teams[0]->attachChildTeam($teams[1]);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[1]->id,
            'parent_id' => $teams[0]->id,
        ]);
    }

    /** @test */
    public function it_attaches_teams_as_child_using_ids(): void
    {
        $teams = Team::factory(2)->create();

        $teams[0]->attachTeam($teams[1]->id);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[1]->id,
            'parent_id' => $teams[0]->id,
        ]);

        $teams = Team::factory(2)->create();

        $teams[0]->attachChildTeam($teams[1]->id);

        $this->assertDatabaseHas('team_team', [
            'child_id' => $teams[1]->id,
            'parent_id' => $teams[0]->id,
        ]);
    }

    /** @test */
    public function team_can_be_related_to_team(): void
    {
        $teamTeam = TeamTeam::factory()->create();

        $this->assertTrue($teamTeam->parent->isRelatedToTeam($teamTeam->child));
        $this->assertTrue($teamTeam->child->isRelatedToTeam($teamTeam->parent));
    }

    /** @test */
    public function team_cant_be_related_to_team(): void
    {
        $teams = Team::factory(2)->create();

        $this->assertFalse($teams[0]->isRelatedToTeam($teams[1]));
        $this->assertFalse($teams[1]->isRelatedToTeam($teams[0]));
    }

    /** @test */
    public function team_can_have_parent_team(): void
    {
        $teamTeam = TeamTeam::factory()->create();

        $this->assertTrue($teamTeam->child->hasParentTeam($teamTeam->parent));

        $this->assertFalse($teamTeam->parent->hasParentTeam($teamTeam->child));
    }

    /** @test */
    public function team_can_have_child_team(): void
    {
        $teamTeam = TeamTeam::factory()->create();

        $this->assertTrue($teamTeam->parent->hasChildTeam($teamTeam->child));

        $this->assertFalse($teamTeam->child->hasChildTeam($teamTeam->parent));
    }
}
