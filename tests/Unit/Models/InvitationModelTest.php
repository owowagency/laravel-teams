<?php

namespace OwowAgency\Teams\Tests\Unit\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use OwowAgency\Teams\Enums\InvitationStatus;
use OwowAgency\Teams\Events\UserInvitedToTeam;
use OwowAgency\Teams\Events\UserJoinedTeam;
use OwowAgency\Teams\Events\UserRequestedToJoinTeam;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Exceptions\InvitationAlreadyDeclined;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Tests\TestCase;

class InvitationModelTest extends TestCase
{
    /** @test */
    public function it_scopes_status_with_integers(): void
    {
        // Create invitation with different status.
        Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $this->assertJsonStructureSnapshot(Invitation::status(InvitationStatus::JOINED)->get());
    }

    /** @test */
    public function it_scopes_status_with_array_of_integers(): void
    {
        // Create invitation with different status.
        Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $this->assertJsonStructureSnapshot(Invitation::status([InvitationStatus::JOINED])->get());
    }

    /** @test */
    public function it_dispatch_events_on_join(): void
    {
        Event::fake([UserJoinedTeam::class]);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::REQUESTED_TO_JOIN,
        ]);

        $invitation->update(['status' => InvitationStatus::JOINED]);

        Event::assertDispatched(UserJoinedTeam::class);
    }

    /** @test */
    public function it_dispatch_events_on_request_to_join(): void
    {
        Event::fake([UserRequestedToJoinTeam::class]);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->update(['status' => InvitationStatus::REQUESTED_TO_JOIN]);

        Event::assertDispatched(UserRequestedToJoinTeam::class);
    }

    /** @test */
    public function it_dispatch_events_on_invite(): void
    {
        Event::fake([UserInvitedToTeam::class]);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->update(['status' => InvitationStatus::INVITED]);

        Event::assertDispatched(UserInvitedToTeam::class);
    }

    /** @test */
    public function it_can_be_accepted(): void
    {
        // Set the carbon instance to test that the timestamp is correctly set
        // in the database.
        Carbon::setTestNow($now = now());

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        $invitation->accept();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::JOINED,
            'accepted_at' => $now,
        ]);
    }

    /** @test */
    public function it_cant_be_accepted_twice(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->accept();
    }

    /** @test */
    public function it_can_be_declined(): void
    {
        // Set the carbon instance to test that the timestamp is correctly set
        // in the database.
        Carbon::setTestNow($now = now());

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::INVITED,
        ]);

        $invitation->decline();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => InvitationStatus::DECLINED,
            'declined_at' => $now,
        ]);
    }

    /** @test */
    public function it_cant_be_declined_twice(): void
    {
        $this->expectException(InvitationAlreadyDeclined::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::DECLINED,
        ]);

        $invitation->decline();
    }

    /** @test */
    public function it_cant_be_declined_when_accepted(): void
    {
        $this->expectException(InvitationAlreadyAccepted::class);

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::JOINED,
        ]);

        $invitation->decline();
    }
}
