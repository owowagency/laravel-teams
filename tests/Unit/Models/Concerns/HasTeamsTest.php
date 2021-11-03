<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Concerns;

use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Tests\Support\Models\User;
use OwowAgency\Teams\Tests\TestCase;

class HasTeamsTest extends TestCase
{
    /** @test */
    public function it_has_teams(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertJsonStructureSnapshot($invitation->user->teams);
    }

    /** @test */
    public function it_asserts_user_in_team_using_model(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->user->belongstoTeam($invitation->model));
    }

    /** @test */
    public function it_asserts_user_in_team_using_integer(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->user->belongstoTeam($invitation->model_id));
    }

    /** @test */
    public function it_asserts_user_not_in_team_using_model(): void
    {
        $user = User::factory()->create();

        $invitation = Invitation::factory()->create();

        $this->assertFalse($user->belongstoTeam($invitation->model));
    }
}
