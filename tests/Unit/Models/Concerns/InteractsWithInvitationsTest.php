<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use OwowAgency\Teams\Enums\InvitationStatus;
use OwowAgency\Teams\Enums\TeamPrivacy;
use OwowAgency\Teams\Events\UserAddedToTeam;
use OwowAgency\Teams\Events\UserDeclinedToJoinTeam;
use OwowAgency\Teams\Events\UserJoinedTeam;
use OwowAgency\Teams\Events\UserLeftTeam;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Exceptions\InvitationAlreadyDeclined;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Tests\Support\Models\User;
use OwowAgency\Teams\Tests\TestCase;

class InteractsWithInvitationsTest extends TestCase
{
    /** @test */
    public function it_receives_users_with_status(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        Invitation::factory()->create([
            'model_id' => $invitation->model_id,
            'status' => InvitationStatus::INVITED,
        ]);

        $this->assertJsonStructureSnapshot($invitation->model->usersWithStatus(InvitationStatus::JOINED));
    }

    /** @test */
    public function it_adds_users_using_model(): void
    {
        // Set the carbon instance to test that the timestamp is correctly set
        // in the database.
        Carbon::setTestNow($now = now());

        Event::fake([UserAddedToTeam::class]);

        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'model_type' => $team->getMorphClass(),
            'model_id' => $team->id,
            'user_id' => $user->id,
            'status' => InvitationStatus::JOINED,
            'accepted_at' => $now,
        ]);

        Event::assertDispatched(UserAddedToTeam::class);
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
            'status' => InvitationStatus::JOINED,
        ]);
    }

    /** @test */
    public function it_adds_users_with_status(): void
    {
        $team = Team::factory()->create();

        $user = User::factory()->create();

        $invitation = $team->addUser($user->id, status: InvitationStatus::INVITED);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'model_type' => $team->getMorphClass(),
            'model_id' => $team->id,
            'user_id' => $user->id,
            'status' => InvitationStatus::INVITED,
        ]);
    }

    /** @test */
    public function it_adds_users_with_correct_status(): void
    {
        $team = Team::factory()->create([
            'privacy' => TeamPrivacy::INVITE_ONLY,
        ]);

        $user = User::factory()->create();

        $invitation = $team->addUser($user->id);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'model_type' => $team->getMorphClass(),
            'model_id' => $team->id,
            'user_id' => $user->id,
            'status' => InvitationStatus::INVITED,
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
        Event::fake([UserLeftTeam::class]);

        $invitation = Invitation::factory()->create();

        $removed = $invitation->model->removeUser($invitation->user);

        $this->assertTrue($removed);

        $this->assertDatabaseMissing('invitations', [
            'model_type' => $invitation->model_type,
            'model_id' => $invitation->model_id,
            'user_id' => $invitation->user_id,
        ]);

        Event::assertDispatched(UserLeftTeam::class);
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
    public function it_can_accept_an_invitation(): void
    {
        Event::fake([UserJoinedTeam::class]);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        $response = $invitation->model->acceptInvitation($invitation->user);

        $this->assertTrue($invitation->is($response));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::JOINED,
        ]);

        Event::assertDispatched(UserJoinedTeam::class);
    }

    /** @test */
    public function it_cant_accept_an_invitation_twice(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->model->acceptInvitation($invitation->user);
    }

    /** @test */
    public function it_cant_accept_an_unfounded_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::REQUESTED_TO_JOIN,
        ]);

        $team = Team::factory()->create();

        $this->assertNull($team->acceptInvitation($invitation->user));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::REQUESTED_TO_JOIN,
        ]);
    }

    /** @test */
    public function it_can_decline_an_invitation(): void
    {
        Event::fake([UserDeclinedToJoinTeam::class]);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        $response = $invitation->model->declineInvitation($invitation->user);

        $this->assertTrue($invitation->is($response));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::DECLINED,
        ]);

        Event::assertDispatched(UserDeclinedToJoinTeam::class);
    }

    /** @test */
    public function it_cant_decline_an_invitation_twice(): void
    {
        $this->expectException(InvitationAlreadyDeclined::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::DECLINED,
        ]);

        $invitation->model->declineInvitation($invitation->user);
    }

    /** @test */
    public function it_cant_decline_an_accepted_invitation(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->model->declineInvitation($invitation->user);
    }

    /** @test */
    public function it_cant_decline_an_unfounded_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::REQUESTED_TO_JOIN,
        ]);

        $team = Team::factory()->create();

        $this->assertNull($team->declineInvitation($invitation->user));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::REQUESTED_TO_JOIN,
        ]);
    }

    /** @test */
    public function it_has_default_invitation_status(): void
    {
        $team = Team::factory()->create();

        $tests = collect([
            TeamPrivacy::OPEN => InvitationStatus::JOINED,
            TeamPrivacy::INVITE_ONLY => InvitationStatus::INVITED,
            TeamPrivacy::REQUESTABLE => InvitationStatus::REQUESTED_TO_JOIN,
        ]);

        foreach ($tests as $privacy => $status) {
            config(['teams.default_privacy' => $privacy]);

            $this->assertEquals($status, $team->defaultInvitationStatus());
        }

        foreach ($tests->sortKeysDesc() as $privacy => $status) {
            $team = Team::factory()->create([
                'privacy' => $privacy,
            ]);

            $this->assertEquals($status, $team->defaultInvitationStatus());
        }
    }
}
