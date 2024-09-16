<?php

namespace Bottelet\TranslationChecker\Finder;

class PersistentKeysManager
{
    /**
     * @var array<string> $keys
     */
    protected array $keys;

    public function __construct()
    {
        $keys = config('translator.persistent_keys', []);

        if (!is_array($keys)) {
            $keys = [];
        }
        $this->keys = $keys;
    }

    public function addKey(string $key): void
    {
        if (!in_array($key, $this->keys, true)) {
            $this->keys[] = $key;
        }
    }

    /** @return array<string> */
    public function getKeys(): array
    {
        return $this->keys;
    }
}
