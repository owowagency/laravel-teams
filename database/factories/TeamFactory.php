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
        $types = config('teams.type')::cases();

        return [
            'name' => $this->faker->name,
            'type' => $types[array_rand($types)]->value,
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
