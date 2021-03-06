<?php

namespace OwowAgency\Teams\Tests\Unit\Models\Query;

use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;
use OwowAgency\Teams\Tests\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function it_includes_accepted_users(): void
    {
        [$team] = $this->prepare();

        $this->assertJsonStructureSnapshot(
            $team->users,
        );
    }

    /** @test */
    public function it_includes_and_accepted_declined_users(): void
    {
        [$team] = $this->prepare();

        $this->assertJsonStructureSnapshot(
            $team->users()->withDeclined()->get(),
        );
    }

    /** @test */
    public function it_includes_declined_users(): void
    {
        [$team] = $this->prepare();

        $this->assertJsonStructureSnapshot(
            $team->users()->withAccepted(false)->withDeclined()->get(),
        );
    }

    /** @test */
    public function it_includes_invited_and_accepted_users(): void
    {
        [$team] = $this->prepare();

        $this->assertJsonStructureSnapshot(
            $team->users()->withOpen()->get(),
        );
    }

    /** @test */
    public function it_includes_invited_users(): void
    {
        [$team] = $this->prepare();

        $this->assertJsonStructureSnapshot(
            $team->users()->withAccepted(false)->withOpen()->get(),
        );
    }

    /** @test */
    public function it_includes_in_count(): void
    {
        [$team] = $this->prepare();

        $this->assertEquals(1, $team->users()->count());
        $this->assertEquals(2, $team->users()->withDeclined()->count());
        $this->assertEquals(3, $team->users()->withDeclined()->withOpen()->count());
    }

    /**
     * Prepare the test.
     */
    private function prepare(): array
    {
        $team = Team::factory()->create();

        // Open invitation.
        Invitation::factory()->forModel($team)->create();

        // Accepted invitation.
        Invitation::factory()->forModel($team)->accepted()->create();

        // Declined invitation.
        Invitation::factory()->forModel($team)->declined()->create();

        return [$team];
    }
}
