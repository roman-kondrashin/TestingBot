<?php

namespace Storage;

interface CacheableInterface {
    const INDEX_KEY = 0;
    const INDEX_VALUE = 1;

    public function saveCache($key, $value);
    public function getCache($key);
}
