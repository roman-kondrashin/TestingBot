<?php

namespace Storage\File;

interface RowPrototypeInterface {
    public function getRow();
    public function hydrate(array $data);
}
