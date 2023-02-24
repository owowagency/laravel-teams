<?php

namespace OwowAgency\Teams\Models\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * Include accepted invitations.
     */
    protected bool $withOtherQuery = false;

    /**
     * Scope the query to include accepted invitations as well.
     */
    public function withAccepted(bool $withAccepted = true): self
    {
        return $this->addWhereNull('accepted_at', $withAccepted);
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withDeclined(bool $withDeclined = true): self
    {
        return $this->addWhereNull('declined_at', $withDeclined);
    }

    /**
     * Scope the query to include declined invitations as well.
     */
    public function withOpen(): self
    {
        $func = $this->withOtherQuery ? 'orWhere' : 'where';

        $this->withOtherQuery = true;

        return $this->$func(function ($query) {
            $query->whereNull('accepted_at')
                ->whereNull('declined_at');
        });
    }

    /**
     * Add or remove a where clause.
     */
    public function addWhereNull(string $column, bool $include): self
    {
        // Get a collection of the current wheres.
        $wheres = collect($this->wheres);

        // Check if the given column already exists in the wheres.
        $exists = $wheres->first(fn ($a) => $a['column'] == $column);

        // Determine whether to use "and" or "or" to combine this condition with other conditions.
        $boolean = $this->withOtherQuery ? 'or' : 'and';

        if ($exists) {
            if ($include) {
                // If it should be included, update the values.
                $exists['boolean'] = $boolean;
                $exists['type'] = 'NotNull';
            } else {
                // If the column should not be included, filter it out of the arrays
                $wheres = $wheres->filter(fn ($a) => $a['column'] != $column);
            }
        } else {
            // If the column does not exist, add it to the wheres array
            $type = 'NotNull';

            $wheres->add(compact('type', 'column', 'boolean'));
        }

        // If neither the 'declined_at' nor 'accepted_at' columns are in the wheres.
        // We need to use 'and' for the next query, so set it to false, otherwise set it to true.
        $this->withOtherQuery = $wheres->contains(fn ($a) => in_array($a['column'], ['declined_at', 'accepted_at']));

        // Set the new wheres array.
        $this->wheres = $wheres->toArray();

        return $this;
    }
}
