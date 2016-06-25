<?php

namespace Application;

use Storage\File\Model\Ini;

class Config extends \SplFixedArray
{
    const KEY_PATH_CSV_CITY = 0;
    const KEY_PATH_CSV_COST = 1;
    const KEY_APPLICATION_STORAGE_OFFSET = 2;

    /**
     * @param Ini $configFile
     */
    public function __construct(Ini $configFile)
    {
        parent::__construct($configFile->getLineCount());
        $this->load($configFile);
    }

    /**
     * @param int $index
     *
     * @return bool|mixed
     */
    public function get($index)
    {
        if ($this->offsetExists($index)) {
            return $this->offsetGet($index);
        }

        return false;
    }

    /**
     * @param Ini $configFile
     */
    private function load(Ini $configFile)
    {
        while (false !== ($row = $configFile->getRow())) {
            $key = strtoupper(str_replace('.', '_', trim($row[0])));
            if (false !== ($index = constant(__CLASS__ . '::KEY_' . $key))) {
                $this[$index] = trim($row[1]);
            }

            $configFile->next();
        }
    }
}
