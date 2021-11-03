<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetupTeamsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->tinyInteger('type')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignIdFor(config('teams.user_model'))
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['model_type', 'model_id', 'user_id']);
        });

        Schema::create('team_team', function (Blueprint $table) {
            $teamModel = new (config('teams.model'));

            $table->foreignIdFor($teamModel, 'parent_id')
                ->constrained($teamModel->getTable())
                ->cascadeOnDelete();

            $table->foreignIdFor($teamModel, 'child_id')
                ->constrained($teamModel->getTable())
                ->cascadeOnDelete();

            $table->primary(['parent_id', 'child_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Yolo
    }
}
