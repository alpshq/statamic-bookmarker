<?php

namespace Alps\Bookmarker\Events;

use Alps\Bookmarker\Data\BookmarkCollection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookmarkCollectionDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public BookmarkCollection $bookmarkCollection)
    {
    }
}
