<?php

namespace Storage\Json;

class JsonObject implements JsonInterface
{
    /**
     * @param mixed $object
     *
     * @return string
     */
    public function encode($object)
    {
        return json_encode($object);
    }

    /**
     * @param string $object
     *
     * @return mixed
     */
    public function decode($object)
    {
        return json_decode($object);
    }
}
