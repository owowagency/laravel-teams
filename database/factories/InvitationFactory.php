<?php

namespace OwowAgency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OwowAgency\Teams\Models\Contracts\HasInvitations;
use OwowAgency\Teams\Models\Invitation;
use OwowAgency\Teams\Models\Team;

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
        return [
            'model_id' => Team::factory(),
            'model_type' => (new Team())->getMorphClass(),
            'user_id' => config('teams.user_model')::factory(),
        ];
    }

    /**
     * Set the morphed model for the given invitable model.
     */
    public function forModel(HasInvitations $invitation)
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => $invitation->getMorphClass(),
            'model_id' => $invitation->id,
        ]);
    }
}
