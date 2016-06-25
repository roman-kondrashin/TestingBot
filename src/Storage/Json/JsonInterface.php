<?php

namespace Storage\Json;

interface JsonInterface
{
    /**
     * @param mixed $object
     *
     * @return string
     */
    public function encode($object);

    /**
     * @param string
     *
     * @return mixed
     */
    public function decode($object);
}
