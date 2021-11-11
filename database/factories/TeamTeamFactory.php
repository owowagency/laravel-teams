<?php

namespace OwowAgency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OwowAgency\Teams\Models\TeamTeam;

class TeamTeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamTeam::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $teamModel = config('teams.model');

        return [
            'parent_id' => $teamModel::factory(),
            'child_id' => $teamModel::factory(),
        ];
    }
}
