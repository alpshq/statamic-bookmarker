<?php

namespace Alps\Bookmarker\Scopes;

use Alps\Bookmarker\Data\Bookmark;
use Alps\Bookmarker\Data\BookmarkCollection;

class Bookmarked extends \Statamic\Query\Scopes\Scope
{
    public function apply($query, $values)
    {
        $value = $this->castValue($values['bookmark_value'] ?? null);

        $collection = BookmarkCollection::user();

        $ids = $collection
            ->items()
            ->filter(function (Bookmark $bookmark) use ($value) {
                if ($value === null) {
                    return true;
                }

                return $bookmark->value === $value;
            })
            ->pluck('id')
            ->all();

        $query->whereIn('id', $ids);
    }

    private function castValue($value): string|int|bool|float|null
    {
        if ($value === null || is_int($value) || is_bool($value) || is_float($value)) {
            return $value;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_numeric($value) && str_contains($value, '.')) {
            return (float) $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $value;
    }
}
