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
    public function it_adds_users_with_role(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user, 'admin');

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => 1,
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
        ]);
    }

    /** @test */
    public function it_adds_users_with_permission(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user, permissions: 1);

        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => 1,
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
        ]);
    }

    /** @test */
    public function it_adds_users_with_role_and_permission(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user, [1], ['edit-users']);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => 1,
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
        ]);

        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => 1,
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
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
    public function it_has_users_using_model(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->model->hasUser($invitation->user));
    }

    /** @test */
    public function it_has_users_using_integer(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertTrue($invitation->model->hasUser($invitation->user_id));
    }

    /** @test */
    public function it_does_not_have_users(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->create();

        $this->assertFalse($team->hasUser($invitation->user));
    }

    /** @test */
    public function it_has_users_with_roles(): void
    {
        $invitation = Invitation::factory()->create()
            ->assignRole(1);

        $this->assertTrue($invitation->model->hasUserWithRole(
            $invitation->user,
            1,
        ));
    }

    /** @test */
    public function it_does_not_have_users_with_roles(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertFalse($invitation->model->hasUserWithRole(
            $invitation->user,
            1,
        ));
    }

    /** @test */
    public function it_has_users_with_permission_to(): void
    {
        $invitation = Invitation::factory()->create()
            ->givePermissionTo('edit-users');

        $this->assertTrue($invitation->model->hasUserWithPermissionTo(
            $invitation->user,
            'edit-users',
        ));
    }

    /** @test */
    public function it_does_not_have_users_with_permission_to(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertFalse($invitation->model->hasUserWithPermissionTo(
            $invitation->user,
            'edit-users',
        ));
    }

    /** @test */
    public function it_removes_users_using_model(): void
    {
        $invitation = Invitation::factory()->create();

        $removed = $invitation->model->removeUser($invitation->user);

        $this->assertTrue($removed);

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

        $this->assertTrue($removed);

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

        $this->assertNull($removed);

        $this->assertDatabaseHas('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);
    }

    /** @test */
    public function it_removes_roles_and_permissions(): void
    {
        $invitation = Invitation::factory()
            ->create()
            ->assignRole(1)
            ->givePermissionTo(1);

        $removed = $invitation->model->removeUser($invitation->user);

        $this->assertTrue($removed);

        $this->assertDatabaseMissing('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);

        $this->assertDatabaseMissing('model_has_roles', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'role_id' => 1,
        ]);

        $this->assertDatabaseMissing('model_has_permissions', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'permission_id' => 1,
        ]);
    }

    /** @test */
    public function it_gets_the_invitation(): void
    {
        $invitation = Invitation::factory()->create();

        $response = $invitation->model->getInvitation($invitation->user);

        $this->assertTrue($invitation->is($response));
    }

    /** @test */
    public function it_does_not_get_the_invitation(): void
    {
        $invitation = Invitation::factory()->create();

        $user = User::factory()->create();

        $this->assertNull($invitation->model->getInvitation($user));
    }
}
