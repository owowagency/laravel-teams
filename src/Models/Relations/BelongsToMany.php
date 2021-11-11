<?php

namespace OwowAgency\Teams\Models\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as BaseBelongsToMany;

class BelongsToMany extends BaseBelongsToMany
{
    /**
     * Include accepted invitations.
     */
    private bool $withAccepted = true;

    /**
     * Include declined invitations.
     */
    private bool $withDeclined = false;

    /**
     * Include open invitations.
     */
    private bool $withOpen = false;

    /**
     * Scope the query to include accepted invitations as well.
     */
    public function withAccepted($withAccepted = true): BelongsToMany
    {
        $this->withAccepted = $withAccepted;

        return $this;
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withDeclined($withDeclined = true): BelongsToMany
    {
        $this->withDeclined = $withDeclined;

        return $this;
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withOpen($withOpen = true): BelongsToMany
    {
        $this->withOpen = $withOpen;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        $this->where(function ($query) {
            if ($this->withAccepted) {
                $query->whereNotNull($this->qualifyPivotColumn('accepted_at'));
            }

            if ($this->withDeclined) {
                $query->orWhereNotNull($this->qualifyPivotColumn('declined_at'));
            }

            if ($this->withOpen) {
                $query->orWhere(function ($query) {
                    $query->whereNull($this->qualifyPivotColumn('accepted_at'))
                        ->whereNull($this->qualifyPivotColumn('declined_at'));
                });
            }
        });

        return parent::get($columns);
    }
}
