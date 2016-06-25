<?php

namespace Storage\File;

class RowFileObject extends FileObject implements RowPrototypeInterface
{
    /** @var string */
    protected $prototype;
    /** @var array */
    protected $row;
    /** @var string */
    protected $divider;
    /** @var string */
    protected $lineEnding;

    /**
     * @param string $fileName
     */
    public function __construct($fileName, $prototype = null)
    {
        parent::__construct($fileName);

        $this->prototype = $prototype;
    }

    /**
     * @return array
     */
    public function getLine()
    {
        return explode($this->divider, rtrim(parent::getLine(), $this->lineEnding));
    }

    /**
     * @return array
     */
    public function getRow()
    {
        if ($this instanceof RowPrototypeInterface && $this->prototype) {
            return $this->hydrate($this->row);
        }

        return $this->row;
    }

    /**
     * @param array $data
     *
     * @return RowPrototypeInterface
     */
    public function hydrate(array $data)
    {
        return new $this->prototype($data);
    }
}
