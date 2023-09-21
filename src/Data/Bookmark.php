<?php

namespace Alps\Bookmarker\Data;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class Bookmark implements Arrayable
{
    public string $id;

    public mixed $value = null;

    public ?Carbon $createdAt = null;

    public static function make(string|array $data): self
    {
        if (is_string($data)) {
            $data = [
                'id' => $data,
            ];
        }

        $instance = new self;

        foreach ($data as $prop => $value) {
            $instance->set($prop, $value);
        }

        return $instance;
    }

    public function set(string $prop, mixed $value): self
    {
        if (! property_exists($this, $prop)) {
            $prop = Str::camel($prop);
        }

        if (! property_exists($this, $prop)) {
            return $this;
        }

        if ($prop === 'createdAt') {
            $value = Carbon::make($value);
        }

        if ($prop === 'value' && ! is_scalar($value)) {
            $value = (string) $value;
        }

        $this->{$prop} = $value;

        return $this;
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        return $this->{$name};
    }

    public function exists(): bool
    {
        return $this->createdAt !== null;
    }

    public function editable(): bool
    {
        return true;
    }

    public function total(mixed $value = true): int
    {
        if (! $this->id) {
            return 0;
        }

        if (! is_scalar($value)) {
            $value = (string) $value;
        }

        return BookmarkCollection::query()
            ->whereNotNull('items.' . $this->id)
            ->where('items.' . $this->id, $value)
            ->count();
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'value' => is_scalar($this->value) ? $this->value : (string) $this->value,
            'created_at' => $this->createdAt ? $this->createdAt->format(DateTimeInterface::W3C) : null,
        ];
    }
}
