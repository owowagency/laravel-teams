<?php

namespace OwowAgency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Models\Contracts\HasInvitations;
use OwowAgency\Teams\Models\Invitation;

class InvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $teamModel = config('teams.model');

        return [
            'model_id' => $teamModel::factory(),
            'model_type' => (new $teamModel())->getMorphClass(),
            'user_id' => config('teams.user_model')::factory(),
            'type' => InvitationType::REQUEST,
            'accepted_at' => null,
            'declined_at' => null,
        ];
    }

    /**
     * Set the morphed model for the given invitable model.
     */
    public function forModel(HasInvitations $invitation): InvitationFactory
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
        ]);
    }

    /**
     * Mark the invitation as accepted.
     */
    public function accepted(): InvitationFactory
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark the invitation as declined.
     */
    public function declined(): InvitationFactory
    {
        return $this->state(fn (array $attributes) => [
            'declined_at' => now(),
        ]);
    }
}
