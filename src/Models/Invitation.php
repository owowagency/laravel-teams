<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use OwowAgency\Database\Factories\InvitationFactory;
use OwowAgency\Teams\Enums\InvitationStatus;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Exceptions\InvitationAlreadyDeclined;
use Spatie\Permission\Traits\HasRoles;

class Invitation extends Pivot
{
    use HasFactory, HasRoles;

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
     * {@inheritdoc}
     */
    protected $casts = [
        'model_id' => 'integer',
        'user_id' => 'integer',
        'status' => InvitationStatus::class,
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

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
     * Scope a query to include only invitations with the given status.
     */
    public function scopeStatus(Builder $query, int|array $status): Builder
    {
        return $query->whereIn(
            'status',
            array_map(fn (int $type) => $type, Arr::wrap($status)),
        );
    }

    /**
     * Accept the invitation.
     */
    public function accept(): void
    {
        if ($this->status->is(InvitationStatus::JOINED)) {
            throw new InvitationAlreadyAccepted();
        }

        $this->update([
            'status' => InvitationStatus::JOINED,
            'accepted_at' => now(),
        ]);
    }

    /**
     * Accept the invitation.
     */
    public function decline(): void
    {
        if ($this->status->is(InvitationStatus::JOINED)) {
            throw new InvitationAlreadyAccepted();
        }

        if ($this->status->is(InvitationStatus::DECLINED)) {
            throw new InvitationAlreadyDeclined();
        }

        $this->update([
            'status' => InvitationStatus::DECLINED,
            'declined_at' => now(),
        ]);
    }

    /**
     * Get the guard name for this model.
     */
    public function guardName(): string
    {
        return config('auth.defaults.guard');
    }

    /**
     * {@inheritdoc}
     */
    protected static function newFactory(): Factory
    {
        return new InvitationFactory();
    }
}
