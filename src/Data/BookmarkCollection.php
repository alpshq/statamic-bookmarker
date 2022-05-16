<?php

namespace Alps\Bookmarker\Data;

use Alps\Bookmarker\Stache\BookmarkQueryBuilder;
use Alps\Bookmarker\Stache\BookmarkStore;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Entries\Entry;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class BookmarkCollection implements ContainsQueryableValues
{
    use FluentlyGetsAndSets, ExistsAsFile, TracksQueriedColumns, TracksQueriedRelations;

    private ?string $id = null;
    private ?string $userId = null;
    private ?string $cookieId = null;
    private Collection|null $items = null;

    public static function query(): BookmarkQueryBuilder
    {
        return new BookmarkQueryBuilder(BookmarkStore::store());
    }

    public static function user(): self
    {
        $instance = null;
        $userId = auth()->id();
        $cookieId = Cookie::get('bookmarker_collection');

        if ($userId) {
            $instance = self::query()->where('user_id', $userId)->first();
        }

        if (!$instance && $cookieId) {
            $instance = self::query()->where('cookie_id', $cookieId)->first();
        }

        if (!$instance) {
            $instance = new self;
            $instance->userId($userId);
        }

        if ($cookieId && $instance->cookieId() !== $cookieId) {
            $instance->cookieId($cookieId);
        }

        return $instance;
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function userId($userId = null)
    {
        return $this->fluentlyGetOrSet('userId')->args(func_get_args());
    }

    public function cookieId($cookieId = null)
    {
        return $this->fluentlyGetOrSet('cookieId')->args(func_get_args());
    }

    public function items($items = null): Collection|self
    {
        return $this
            ->fluentlyGetOrSet('items')
            ->setter(function($items) {
                return collect($items)
                    ->map(function($data) {
                        if ($data instanceof Bookmark) {
                            return $data;
                        }

                        return Bookmark::make($data);
                    })
                    ->values();
            })
            ->getter(function ($items) {
                return collect($items);
            })
            ->args(func_get_args());
    }

//    public function itemIds(): array
//    {
//        $foo = $this->items()->map(function(Bookmark $bookmark) {
//            return $bookmark->id . '=' . $bookmark->value;
//        })->all();
//
//        $foo = $this->items()->pluck('value', 'id')->all();
//
//        dump($foo);
//
//        return $foo;
//    }

    public function getBookmark($entryOrId): ?Bookmark
    {
        $id = $this->getEntryId($entryOrId);

        return $this->items()->first(fn (Bookmark $bookmark) => $bookmark->id === $id);
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        $this->removeBookmark($bookmark);

        $items = $this->items()->all();

//        $existingIds = array_column($items, 'id');
//        $idx = array_search($bookmark->id, $existingIds);
//
//        if ($idx !== false) {
//            unset($items[$idx]);
//            $items = array_values($items);
//        }

        $items[] = $bookmark;

        $this->items($items);

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        $items = $this->items()->filter(function (Bookmark $savedBookmark) use ($bookmark) {
            return $savedBookmark->id !== $bookmark->id;
        });

        $this->items($items);

        return $this;
    }

    public function getQueryableValue(string $field)
    {
        if (substr($field, 0, 6) === 'items.') {
            $id = substr($field, 6);

            return $this->getBookmark($id)?->value;
        }

        return $this->value(Str::camel($field));
    }

    private function getEntryId($entryOrId): string
    {
        if ($entryOrId instanceof Entry) {
            return $entryOrId->id();
        }

        return (string) $entryOrId;
    }

    public function save()
    {
        if (!$this->id()) {
            $this->id(Stache::generateId());
        }

        BookmarkStore::store()->save($this);

        return true;
    }

    public function delete()
    {
        BookmarkStore::store()->delete($this);

        return true;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(BookmarkStore::store()->directory(), '/'),
            $this->id(),
        ]);
    }

    public function fileData()
    {
        return [
            'id' => $this->id(),
            'user_id' => $this->userId(),
            'cookie_id' => $this->cookieId(),
            'items' => $this->items()->toArray(),
        ];
    }

    public function value($key)
    {
        return $this->get($key);
    }

    public function get($key, $fallback = null)
    {
        return $this->{$key}() ?? $fallback;
    }

}
