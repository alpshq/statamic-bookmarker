<?php

namespace Alps\Bookmarker\Stache;

class BookmarkQueryBuilder extends \Statamic\Stache\Query\Builder
{
    protected function getFilteredKeys()
    {
        if (! empty($this->wheres)) {
            return $this->getKeysWithWheres();
        }

        return collect($this->store->paths()->keys());
    }

    private function getKeysWithWheres()
    {
        return collect($this->wheres)->reduce(function ($ids, $where) {
            $items = $this->store->index($where['column'])->items();

            if ($where['type'] === 'NotNull') {
                $keys = $this->filterWhereNotNull($items, $where)->keys();
            }

            if ($where['type'] === 'Null') {
                $keys = $this->filterWhereNull($items, $where)->keys();
            }

            if ($where['type'] === 'Basic') {
                $keys = $this->filterWhereBasic($items, $where)->keys();
            }

            // Perform the filtering, and get the keys (the references, we don't care about the values).

            // Continue intersecting the keys across the where clauses.
            // If a key exists in the reduced array but not in the current iteration, it should be removed.
            // On the first iteration, there's nothing to intersect, so just use the result as a starting point.
            return $ids ? $ids->intersect($keys)->values() : $keys;
        });
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)->items()->all();

            return [$orderBy->sort => $items];
        });
    }
}
