<?php

namespace Alps\Bookmarker\Stache;

use Alps\Bookmarker\Data\BookmarkCollection;
use Alps\Bookmarker\Events\BookmarkCollectionDeleted;
use Alps\Bookmarker\Events\BookmarkCollectionSaved;
use Statamic\Facades\Stache as StacheFacade;
use Statamic\Facades\YAML;

class BookmarkStore extends \Statamic\Stache\Stores\BasicStore
{
    public static function store(): self
    {
        return \Statamic\Facades\Stache::store(self::key());
    }

    public static function key()
    {
        return 'bookmarks';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        $bookmark = new BookmarkCollection;

        $keyMap = [
            'id',
            'user_id' => 'userId',
            'cookie_id' => 'cookieId',
            'items',
        ];

        foreach ($keyMap as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }

            if (array_key_exists($key, $data)) {
                $bookmark->{$value}($data[$key]);
            }
        }

        //        if (array_key_exists('id', $data)) {
        //            $bookmark->id($data['id']);
        //        }
        //
        //        if (array_key_exists('user_id', $data)) {
        //            $bookmark->userId($data['user_id']);
        //        }
        //
        //        if (array_key_exists('items', $data)) {
        //            $bookmark->items($data['items']);
        //        }

        if ($bookmark->id()) {
            return $bookmark;
        }

        $bookmark->id(StacheFacade::generateId());
        $bookmark->save();

        return $bookmark;
    }

    /**
     * @param BookmarkCollection $item
     */
    public function save($item)
    {
        if (! $item->id()) {
            $item->id(StacheFacade::generateId());
        }

        if (! $item->cookieId()) {
            $item->cookieId(StacheFacade::generateId());
        }

        parent::save($item);

        cookie()->queue('bookmarker_collection', $item->cookieId(), 365 * 24 * 60);

        BookmarkCollectionSaved::dispatch($item);
    }

    public function delete($item)
    {
        parent::delete($item);

        BookmarkCollectionDeleted::dispatch($item);
    }
}
