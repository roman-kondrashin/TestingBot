<?php

namespace Storage;

trait CacheableTrait {
    /** @var \SplQueue */
    private $cache;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function saveCache($key, $value)
    {
        if ($this instanceof CacheableInterface) {
            if (null === $this->getCache($key)) {
                $data = new \SplFixedArray(2);
                $data[0] = $key;
                $data[1] = $value;

                $this->cache->push($data);
            }
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getCache($key)
    {
        if ($this instanceof CacheableInterface) {
            if (!($this->cache instanceof \SplQueue)) {
                $this->cache = new \SplQueue();
            }
        }

        return $this->searchCache($key);
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    private function searchCache($key)
    {
        if ($this instanceof CacheableInterface) {
            $obj = $this->cache;
            $obj->rewind();

            while ($obj->valid()) {
                /** @var \SplFixedArray $current */
                $current = $obj->current();
                if ($current[CacheableInterface::INDEX_KEY] == $key) {
                    return $current[CacheableInterface::INDEX_VALUE];
                }

                $obj->next();
            }
        }

        return null;
    }
}
