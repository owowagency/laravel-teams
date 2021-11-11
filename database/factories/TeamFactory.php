<?php

namespace OwowAgency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'creator_id' => null,
            'type' => null,
            'privacy' => null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function modelName(): string
    {
        return config('teams.model');
    }
}
