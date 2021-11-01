<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Concerns;

use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Tests\Support\Models\User;
use OwowAgency\Teams\Tests\TestCase;

class InteractsWithInvitationsTest extends TestCase
{
    /** @test */
    public function it_adds_users_using_model(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'model_type' => $team->getMorphClass(),
            'model_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_adds_users_using_id(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user->id);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'model_type' => $team->getMorphClass(),
            'model_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_does_not_add_users_twice(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->forModel($team)->create();

        $this->assertTrue($invitation->is($team->addUser($invitation->user)));

        $this->assertDatabaseCount('invitations', 1);
    }

    /** @test */
    public function it_removes_users_using_model(): void
    {
        $invitation = Invitation::factory()->create();

        $removed = $invitation->model->removeUser($invitation->user);

        $this->assertEquals(1, $removed);

        $this->assertDatabaseMissing('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);
    }

    /** @test */
    public function it_removes_users_using_id(): void
    {
        $invitation = Invitation::factory()->create();

        $removed = $invitation->model->removeUser($invitation->user_id);

        $this->assertEquals(1, $removed);

        $this->assertDatabaseMissing('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);
    }

    /** @test */
    public function it_does_not_remove_users_from_other_teams(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->create();

        $removed = $team->removeUser($invitation->user);

        $this->assertEquals(0, $removed);

        $this->assertDatabaseHas('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);
    }
}
