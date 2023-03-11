<?php

namespace Alps\Bookmarker\Http\Controllers;

use Alps\Bookmarker\Data\Bookmark;
use Alps\Bookmarker\Data\BookmarkCollection;
use Alps\Bookmarker\Services\PayloadHasher;
use Alps\Bookmarker\Stache\BookmarkStore;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\StaticCaching\Cacher;
use Statamic\View\Antlers\Antlers;
use Statamic\View\View;

class SubmitController extends Controller
{
    private array|null $payload = null;

    public function __construct(
        private Antlers       $antlers,
        private PayloadHasher $payloadHasher,
        private Cacher        $cacher
    )
    {
    }

    public function handlePost(Request $request)
    {
        $id = $request->input('id');
        $value = $request->input('value', true);

        $value = $this->castValue($value);

        $bookmark = Bookmark::make([
            'id' => $id,
            'value' => $value,
            'createdAt' => now(),
        ]);

        $bookmarkCollection = BookmarkCollection::user();
        $bookmarkCollection->addBookmark($bookmark);
        $bookmarkCollection->save();

        $this->invalidatePageCache($request);

        if (!$request->isXmlHttpRequest()) {
            return redirect()->back(201);
        }

        return $this->renderTemplate($request, $bookmark);
    }

    public function handleDelete(Request $request)
    {
        $id = $request->input('id');

        $bookmarkCollection = BookmarkCollection::user();

        $bookmark = $bookmarkCollection->getBookmark($id);

        if ($bookmark) {
            $bookmarkCollection->removeBookmark($bookmark);
            $bookmarkCollection->save();
        }

        $this->invalidatePageCache($request);

        if (!$request->isXmlHttpRequest()) {
            return redirect()->back(201);
        }

        $bookmark = Bookmark::make($id);

        return $this->renderTemplate($request, $bookmark);
    }

    private function invalidatePageCache(Request $request): self
    {
        $path = $this->getPayload($request)['data']['path'] ?? null;

        $this->cacher->getDomains()->each(function($domain) use ($path) {
            $url = '/' . ltrim($path, '/');
            $this->cacher->invalidateUrl($url, $domain);
        });

        return $this;
    }

    private function getPayload(Request $request): array
    {
        if ($this->payload !== null) {
            return $this->payload;
        }

        $payload = $request->input('payload');
        $payload = urldecode($payload);

        $payloadSignature = $request->input('payload_signature');

        if (!$this->payloadHasher->verify($payload, $payloadSignature)) {
            abort(401, 'Payload signature invalid.');
        }

        return $this->payload = $this->payloadHasher->parsePayload($payload);
    }

    private function renderTemplate(Request $request, Bookmark $bookmark)
    {
        $payload = $this->getPayload($request);

        $content = $payload['content'] ?? null;
        $data = $payload['viewData'] ?? [];

        if (!$content) {
            return null;
        }

        $data = array_merge($data, [
            'bookmark' => $bookmark,
        ]);

        return $this->antlers->parse($content, $data)->withoutExtractions();
    }

    private function castValue(mixed $value): string|int|bool|float|null
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
