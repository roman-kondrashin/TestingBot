<?php

namespace Storage\File;

class FileObject extends \SplFileObject implements FileObjectInterface
{
    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        parent::__construct($fileName);

        $this->setFlags(
              \SplFileObject::READ_AHEAD
            | \SplFileObject::DROP_NEW_LINE
            | \SplFileObject::SKIP_EMPTY
        );
        $this->rewind();
    }

    /**
     * @return int
     */
    public function getLineCount()
    {
        $res = 0;

        while (!$this->eof()) {
            $res++;
            $this->next();
        }

        $this->rewind();

        return $res;
    }

    /**
     * @return string
     */
    protected function getLine()
    {
        return $this->current();
    }
}
