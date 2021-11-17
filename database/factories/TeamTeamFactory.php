<?php

namespace OwowAgency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $teamModel = config('teams.models.team');

        return [
            'parent_id' => $teamModel::factory(),
            'child_id' => $teamModel::factory(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function modelName(): string
    {
        return config('teams.models.team_team');
    }
}
