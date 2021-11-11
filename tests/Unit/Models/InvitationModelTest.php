<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

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
use OwowAgency\Teams\Tests\TestCase;

class InvitationModelTest extends TestCase
{
    /** @test */
    public function it_scopes_type_with_integers(): void
    {
        // Create invitation with different type.
        Invitation::factory()->create([
            'type' => InvitationType::INVITATION,
        ]);

        Invitation::factory()->create([
            'type' => InvitationType::REQUEST,
        ]);

        $this->assertJsonStructureSnapshot(Invitation::type(InvitationType::REQUEST)->get());
    }
    /** @test */
    public function it_scopes_accepted_invitations(): void
    {
        Invitation::factory()->declined()->create();

        Invitation::factory()->accepted()->create();

        $this->assertJsonStructureSnapshot(Invitation::accepted()->get());
    }
    /** @test */
    public function it_scopes_declined_invitations(): void
    {
        Invitation::factory()->declined()->create();

        Invitation::factory()->accepted()->create();

        $this->assertJsonStructureSnapshot(Invitation::declined()->get());
    }

    /** @test */
    public function it_dispatch_events_on_invite(): void
    {
        Event::fake([UserInvitedToTeam::class]);

        $invitation = Invitation::factory()->create([
            'type' => InvitationType::INVITATION,
        ]);

        Event::assertDispatched(fn (UserInvitedToTeam $e) => $e->invitation->is($invitation));
    }

    /** @test */
    public function it_dispatch_events_on_request_to_join(): void
    {
        Event::fake([UserRequestedToJoinTeam::class]);

        $invitation = Invitation::factory()->create([
            'type' => InvitationType::REQUEST,
        ]);

        Event::assertDispatched(fn (UserRequestedToJoinTeam $e) => $e->invitation->is($invitation));
    }

    /** @test */
    public function it_dispatch_events_on_deletion(): void
    {
        Event::fake([UserLeftTeam::class]);

        $invitation = Invitation::factory()->accepted()->create();

        $invitation->delete();

        Event::assertDispatched(fn (UserLeftTeam $e) => $e->invitation->is($invitation));
    }

    /** @test */
    public function it_doest_not_dispatch_events_on_deletion(): void
    {
        Event::fake([UserLeftTeam::class]);

        $invitation = Invitation::factory()->create();

        $invitation->delete();

        Event::assertNotDispatched(UserLeftTeam::class);
    }

    /** @test */
    public function it_can_be_accepted(): void
    {
        // Set the carbon instance to test that the timestamp is correctly set
        // in the database.
        Carbon::setTestNow($now = now());

        Event::fake([UserJoinedTeam::class]);

        $invitation = Invitation::factory()->create();

        $invitation->accept();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'accepted_at' => $now,
        ]);

        Event::assertDispatched(fn (UserJoinedTeam $e) => $e->invitation->is($invitation));
    }

    /** @test */
    public function it_cant_be_accepted_twice(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->accepted()->create();

        $invitation->accept();
    }

    /** @test */
    public function it_can_be_declined(): void
    {
        // Set the carbon instance to test that the timestamp is correctly set
        // in the database.
        Carbon::setTestNow($now = now());

        Event::fake([UserDeclinedToJoinTeam::class]);

        $invitation = Invitation::factory()->create();

        $invitation->decline();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'declined_at' => $now,
        ]);

        Event::assertDispatched(fn (UserDeclinedToJoinTeam $e) => $e->invitation->is($invitation));
    }

    /** @test */
    public function it_cant_be_declined_twice(): void
    {
        $this->expectException(InvitationAlreadyDeclined::class);

        $invitation = Invitation::factory()->declined()->create();

        $invitation->decline();
    }

    /** @test */
    public function it_cant_be_declined_when_accepted(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->accepted()->create();

        $invitation->decline();
    }
}
