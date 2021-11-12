<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Events\UserDeclinedToJoinTeam;
use OwowAgency\Teams\Events\UserInvitedToTeam;
use OwowAgency\Teams\Events\UserJoinedTeam;
use OwowAgency\Teams\Events\UserLeftTeam;
use OwowAgency\Teams\Events\UserRequestedToJoinTeam;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Exceptions\InvitationAlreadyDeclined;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Tests\Support\Models\User;
use OwowAgency\Teams\TeamAssertions;
use OwowAgency\Teams\Tests\TestCase;

class InteractsWithInvitationsTest extends TestCase
{
    use TeamAssertions;

    /** @test */
    public function it_receives_users_with_type(): void
    {
        $invitation = Invitation::factory()->accepted()->create([
            'type' => InvitationType::REQUEST,
        ]);

        Invitation::factory()->create([
            'model_id' => $invitation->model_id,
            'type' => InvitationType::INVITATION,
        ]);

        $response = $invitation->model->users()
            ->wherePivot('type', InvitationType::REQUEST)
            ->get();

        $this->assertJsonStructureSnapshot($response);
    }

    /** @test */
    public function it_adds_users_using_model_as_invitation(): void
    {
        Event::fake([UserInvitedToTeam::class]);

        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->addUser($user, InvitationType::INVITATION);

        $this->assertUserInvitedToTeam($user, $team);

        Event::assertDispatched(UserInvitedToTeam::class);
    }

    /** @test */
    public function it_adds_users_using_id_as_request(): void
    {
        Event::fake([UserRequestedToJoinTeam::class]);

        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->addUser($user->id, InvitationType::REQUEST);

        $this->assertUserRequestedToJoinTeam($user, $team);

        Event::assertDispatched(UserRequestedToJoinTeam::class);
    }

    /** @test */
    public function it_invites_users_using_model(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->inviteUser($user, InvitationType::INVITATION);

        $this->assertUserInvitedToTeam($user, $team);
    }

    /** @test */
    public function it_requests_users_using_model(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->requestToJoin($user);

        $this->assertUserRequestedToJoinTeam($user, $team);
    }

    /** @test */
    public function it_adds_users_with_role(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->inviteUser($user, 'admin');

        $this->assertUserInvitedToTeam($user, $team, assertWithRoles: 'admin');
    }

    /** @test */
    public function it_adds_users_with_permission(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->inviteUser($user, permissions: 1);

        $this->assertUserInvitedToTeam($user, $team, assertWithPermissions: 1);
    }

    /** @test */
    public function it_adds_users_with_role_and_permission(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $team->inviteUser($user, [1], ['edit-users']);

        $this->assertUserInvitedToTeam($user, $team, 1, 'edit-users');
    }

    /** @test */
    public function it_does_not_add_users_twice(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->forModel($team)->create();

        $this->assertTrue($invitation->is($team->inviteUser($invitation->user)));

        $this->assertDatabaseCount('invitations', 1);
    }

    /** @test */
    public function it_has_users(): void
    {
        $invitation = Invitation::factory()->accepted()->create();

        $this->assertTrue($invitation->model->hasUser($invitation->user));
        $this->assertTrue($invitation->model->hasUser($invitation->user_id));
    }

    /** @test */
    public function it_does_not_have_unaccepted_users(): void
    {
        $invitation = Invitation::factory()->create();

        $this->assertFalse($invitation->model->hasUser($invitation->user));
    }

    /** @test */
    public function it_does_not_have_users(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->create();

        $this->assertFalse($team->hasUser($invitation->user));
    }

    /** @test */
    public function it_does_not_have_declined_users(): void
    {
        $invitation = Invitation::factory()->declined()->create();

        $this->assertFalse($invitation->model->hasUser($invitation->user));
    }

    /** @test */
    public function it_has_users_with_roles(): void
    {
        $invitation = Invitation::factory()->accepted()->create()->assignRole(1);

        $this->assertTrue($invitation->model->hasUserWithRole(
            $invitation->user,
            1,
        ));
    }

    /** @test */
    public function it_does_not_have_users_with_roles(): void
    {
        $invitation = Invitation::factory()->accepted()->create();

        $this->assertFalse($invitation->model->hasUserWithRole(
            $invitation->user,
            1,
        ));
    }

    /** @test */
    public function it_has_users_with_permission_to(): void
    {
        $invitation = Invitation::factory()->accepted()->create()
            ->givePermissionTo('edit-users');

        $this->assertTrue($invitation->model->hasUserWithPermissionTo(
            $invitation->user,
            'edit-users',
        ));
    }

    /** @test */
    public function it_does_not_have_users_with_permission_to(): void
    {
        $invitation = Invitation::factory()->accepted()->create();

        $this->assertFalse($invitation->model->hasUserWithPermissionTo(
            $invitation->user,
            'edit-users',
        ));
    }

    /** @test */
    public function it_removes_users_using_model(): void
    {
        Event::fake([UserLeftTeam::class]);

        $invitation = Invitation::factory()->accepted()->create();

        $removed = $invitation->model->removeUser($invitation->user);

        $this->assertTrue($removed);

        $this->assertUserNotInAnyTeam($invitation->user, $invitation->model);

        Event::assertDispatched(UserLeftTeam::class);
    }

    /** @test */
    public function it_removes_users_using_id(): void
    {
        $invitation = Invitation::factory()->create();

        $removed = $invitation->model->removeUser($invitation->user_id);

        $this->assertTrue($removed);

        $this->assertUserNotInAnyTeam($invitation->user, $invitation->model);
    }

    /** @test */
    public function it_does_not_remove_users_from_other_teams(): void
    {
        $team = Team::factory()->create();

        $invitation = Invitation::factory()->create();

        $removed = $team->removeUser($invitation->user);

        $this->assertNull($removed);

        $this->assertUserRequestedToJoinTeam($invitation->user, $invitation->model);
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
    public function it_can_accept_an_invitation(): void
    {
        Carbon::setTestNow($now = now());

        Event::fake([UserJoinedTeam::class]);

        $invitation = Invitation::factory()->create([
            'type' => InvitationType::INVITATION,
        ]);

        $response = $invitation->model->acceptInvitation($invitation->user);

        $this->assertTrue($invitation->is($response));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'type' => InvitationType::INVITATION,
            'accepted_at' => $now,
        ]);

        Event::assertDispatched(UserJoinedTeam::class);
    }

    /** @test */
    public function it_cant_accept_an_invitation_twice(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->accepted()->create([
            'type' => InvitationType::REQUEST,
        ]);

        $invitation->model->acceptInvitation($invitation->user);
    }

    /** @test */
    public function it_cant_accept_an_unknown_invitation(): void
    {
        $invitation = Invitation::factory()->create();

        // Different team.
        $team = Team::factory()->create();

        $this->assertNull($team->acceptInvitation($invitation->user));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'accepted_at' => null,
        ]);
    }

    /** @test */
    public function it_can_decline_an_invitation(): void
    {
        Carbon::setTestNow($now = now());

        Event::fake([UserDeclinedToJoinTeam::class]);

        $invitation = Invitation::factory()->create([
            'type' => InvitationType::INVITATION,
        ]);

        $response = $invitation->model->declineInvitation($invitation->user);

        $this->assertTrue($invitation->is($response));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'declined_at' => $now,
        ]);

        Event::assertDispatched(UserDeclinedToJoinTeam::class);
    }

    /** @test */
    public function it_cant_decline_an_invitation_twice(): void
    {
        $this->expectException(InvitationAlreadyDeclined::class);

        $invitation = Invitation::factory()->declined()->create();

        $invitation->model->declineInvitation($invitation->user);
    }

    /** @test */
    public function it_cant_decline_an_accepted_invitation(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->accepted()->create();

        $invitation->model->declineInvitation($invitation->user);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'declined_at' => null,
        ]);
    }

    /** @test */
    public function it_cant_decline_an_unknown_invitation(): void
    {
        $invitation = Invitation::factory()->create();

        // Different team.
        $team = Team::factory()->create();

        $this->assertNull($team->declineInvitation($invitation->user));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'declined_at' => null,
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
