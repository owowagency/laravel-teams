<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwowAgency\Database\Factories\InvitationFactory;

class Invitation extends Pivot
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The table associated with the model.
     *
     * We force the table name here so that all child models share the same
     * table.
     *
     * @var string
     */
    protected $table = 'invitations';

    /**
     * The morph to relation to the related model.
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * The belongs to relationship to the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('teams.user_model'));
    }

    /**
     * {@inheritdoc}
     */
    protected static function newFactory(): Factory
    {
        return new InvitationFactory();
    }
}
