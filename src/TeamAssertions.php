<?php

namespace OwowAgency\Teams;

use Illuminate\Database\Eloquent\Model;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;

trait TeamAssertions
{
    /**
     * Assert that the user is in the given team. The invitation should be
     * accepted.
     */
    public function assertUserInTeam(
        Model|int $user,
        Team|int $team,
        $assertInvitationType = null,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            0,
            $assertInvitationType,
            $assertWithRoles,
            $assertWithPermissions,
        );
    }

    /**
     * Assert that the user is not in the given team. It doesn't check
     * invitation status.
     */
    public function assertUserNotInTeam(
        Model|int $user,
        Team|int $team,
        $assertInvitationType = null,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            null,
            $assertInvitationType,
            $assertWithRoles,
            $assertWithPermissions,
            false,
        );
    }

    /**
     * Assert that the user is in any team. The invitation should be accepted.
     */
    public function assertUserInAnyTeam(
        Model|int $user,
        $assertInvitationType = null,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            null,
            0,
            $assertInvitationType,
            $assertWithRoles,
            $assertWithPermissions,
        );
    }

    /**
     * Assert that the user is not in any team. It doesn't check invitation
     * status.
     */
    public function assertUserNotInAnyTeam(
        Model|int $user,
        $assertInvitationType = null,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            null,
            null,
            $assertInvitationType,
            $assertWithRoles,
            $assertWithPermissions,
            false,
        );
    }

    /**
     * Assert that the user is invited to the given team.
     */
    public function assertUserInvitedToTeam(
        Model|int $user,
        Team|int $team,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            2,
            InvitationType::INVITATION,
            $assertWithRoles,
            $assertWithPermissions,
        );
    }

    /**
     * Assert that the user is not currently invited to the given team.
     */
    public function assertUserNotInvitedToTeam(
        Model|int $user,
        Team|int $team,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            2,
            InvitationType::INVITATION,
            $assertWithRoles,
            $assertWithPermissions,
            false,
        );
    }

    /**
     * Assert that the user is invited to the given team.
     */
    public function assertUserRequestedToJoinTeam(
        Model|int $user,
        Team|int $team,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            2,
            InvitationType::REQUEST,
            $assertWithRoles,
            $assertWithPermissions,
        );
    }

    /**
     * Assert that the user is not currently invited to the given team.
     */
    public function assertUserNotRequestedToJoinTeam(
        Model|int $user,
        Team|int $team,
        $assertWithRoles = null,
        $assertWithPermissions = null,
    ): void {
        $this->handleUserInTeamAssertion(
            $user->id ?? $user,
            $team->id ?? $team,
            2,
            InvitationType::REQUEST,
            $assertWithRoles,
            $assertWithPermissions,
            false,
        );
    }

    /**
     * Handle the assertion of users in teams.
     */
    private function handleUserInTeamAssertion(
        int $userId,
        int $teamId = null,
        int $assertInvitationStatus = null,
        $assertInvitationType = null,
        $assertWithRoles = null,
        $assertWithPermissions = null,
        bool $exists = true,
    ): void {
        $invitation = Invitation::where('user_id', $userId)
            ->where('model_type', (new Team())->getMorphClass());

        // Check if we should assert that the user is invited to a certain team.
        if ($teamId !== null) {
            $invitation->where('model_id', $teamId);
        }

        // Check if we should assert that the user has a certain invitation
        // status. 0 = accepted invitation, 1 = declined invitation, 2 = open
        // invitation.
        if ($assertInvitationStatus === 0) {
            $invitation->whereNotNull('accepted_at');
        } elseif ($assertInvitationStatus === 1) {
            $invitation->whereNotNull('declined_at');
        } elseif ($assertInvitationStatus === 2) {
            $invitation->whereNull('accepted_at')
                ->whereNull('declined_at');
        }

        // Check if we need to assert the invitation type.
        if ($assertInvitationType !== null) {
            $invitation->where('type', $assertInvitationType);
        }

        // Check if we need to assert that the user has the given roles.
        if ($assertWithRoles !== null) {
            $invitation->role($assertWithRoles);
        }

        // Check if we need to assert that the user has the given permissions.
        if ($assertWithPermissions !== null) {
            $invitation->permission($assertWithPermissions);
        }

        if ($exists) {
            $this->assertTrue($invitation->exists(), 'The user is not part of the/a team.');
        } else {
            $this->assertFalse($invitation->exists(), 'The user is part of the/a team.');
        }
    }
}
