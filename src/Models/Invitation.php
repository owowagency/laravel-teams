<?php

namespace OwowAgency\Teams\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use OwowAgency\Database\Factories\InvitationFactory;
use OwowAgency\Teams\Enums\InvitationType;
use OwowAgency\Teams\Exceptions\InvitationAlreadyAccepted;
use OwowAgency\Teams\Exceptions\InvitationAlreadyDeclined;
use OwowAgency\Teams\Exceptions\InvitationAlreadyReopened;
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
        'type' => InvitationType::class,
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
     * Scope a query to include only invitations with the given type.
     */
    public function scopeType(Builder $query, int|array $type): Builder
    {
        return $query->whereIn(
            'type',
            array_map(fn (int $type) => $type, Arr::wrap($type)),
        );
    }

    /**
     * Scope a query to include only invitations which are accepted.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNotNull('accepted_at')
                ->whereNull('declined_at');
        });
    }

    /**
     * Scope a query to include only invitations which are declined.
     */
    public function scopeDeclined(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNotNull('declined_at')
                ->whereNull('accepted_at');
        });
    }

    /**
     * Scope a query to include only open invitations.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('accepted_at')
                ->whereNull('declined_at');
        });
    }

    /**
     * Accept the invitation.
     */
    public function accept(): Invitation
    {
        throw_if($this->accepted_at !== null, InvitationAlreadyAccepted::class);

        throw_if($this->declined_at !== null, InvitationAlreadyDeclined::class);

        $this->update([
            'accepted_at' => now(),
        ]);

        return $this;
    }

    /**
     * Decline the invitation.
     */
    public function decline(): Invitation
    {
        throw_if($this->accepted_at !== null, InvitationAlreadyAccepted::class);

        throw_if($this->declined_at !== null, InvitationAlreadyDeclined::class);

        $this->update([
            'declined_at' => now(),
        ]);

        return $this;
    }

    /**
     * Open the declined invitation.
     */
    public function reopen(): Invitation
    {
        throw_if($this->declined_at === null, InvitationAlreadyReopened::class);

        $this->update([
            'declined_at' => null,
        ]);

        return $this;
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
    protected static function newFactory()
    {
        return new InvitationFactory();
    }
}
