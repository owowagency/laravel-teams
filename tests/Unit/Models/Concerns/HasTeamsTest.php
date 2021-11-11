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
    public function it_has_user_in_team_using_model(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->user->belongstoTeam($invitation->model));
    }

    /** @test */
    public function it_has_user_in_team_using_integer(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->user->belongstoTeam($invitation->model_id));
    }

    /** @test */
    public function it_has_user_not_in_team_using_model(): void
    {
        $user = User::factory()->create();

        $invitation = Invitation::factory()->create();

        $this->assertFalse($user->belongstoTeam($invitation->model));
    }

    /** @test */
    public function it_has_user_in_team_with_roles(): void
    {
        $invitation = Invitation::factory()
            ->create()
            ->assignRole(1);

        $this->assertTrue($invitation->user->hasTeamRole($invitation->model, 1));
    }

    /** @test */
    public function it_does_not_have_user_in_team_with_roles(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertFalse($invitation->user->hasTeamRole($invitation->model, 1));
    }

    /** @test */
    public function it_has_user_in_team_with_permission_to(): void
    {
        $invitation = Invitation::factory()
            ->create()
            ->givePermissionTo('edit-users');

        $this->assertTrue($invitation->user->hasTeamPermissionTo(
            $invitation->model,
            'edit-users',
        ));
    }

    /** @test */
    public function it_does_not_have_user_in_team_with_permission_to(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertFalse($invitation->user->hasTeamPermissionTo(
            $invitation->model,
            'edit-users',
        ));
    }
}
