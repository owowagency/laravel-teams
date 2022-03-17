<?php

namespace OwowAgency\Teams\Models\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * Include accepted invitations.
     */
    protected bool $withAccepted = true;

    /**
     * Include declined invitations.
     */
    protected bool $withDeclined = false;

    /**
     * Include open invitations.
     */
    protected bool $withOpen = false;

    /**
     * Scope the query to include accepted invitations as well.
     */
    public function withAccepted($withAccepted = true): self
    {
        $this->withAccepted = $withAccepted;

        return $this;
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withDeclined($withDeclined = true): self
    {
        $this->withDeclined = $withDeclined;

        return $this;
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withOpen($withOpen = true): self
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
                $query->whereNotNull('accepted_at');
            }

            if ($this->withDeclined) {
                $query->orWhereNotNull('declined_at');
            }

            if ($this->withOpen) {
                $query->orWhere(function ($query) {
                    $query->whereNull('accepted_at')
                        ->whereNull('declined_at');
                });
            }
        });

        return parent::get($columns);
    }
}
