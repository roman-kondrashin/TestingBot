<?php

namespace Storage\File\Model;

use Storage\File\RowFileObject;

class Ini extends RowFileObject
{
    /** @var string */
    protected $divider = '=';
    /** @var string */
    protected $lineEnding = ';';

    /**
     * @return array|bool
     */
    public function getRow()
    {
        if ($this->valid()) {
            $this->row = $this->getLine();

            return parent::getRow();
        }

        return false;
    }
}
